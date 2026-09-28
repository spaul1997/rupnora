<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class EmailCampaign extends Model
{
    public const TEMPLATES = [
        'new-offer-launch' => 'New Offer Launch',
        'new-design-launch' => 'New Design Launch',
        'influencer-proposal' => 'Influencer Collaboration Proposal',
        'influencer-agreement' => 'Influencer Agreement',
    ];

    public const STATUSES = [
        'queued' => 'Queued',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'partial' => 'Partially Sent',
        'failed' => 'Failed',
    ];

    protected $fillable = [
        'created_by',
        'name',
        'template_type',
        'subject',
        'preheader',
        'eyebrow',
        'headline',
        'body',
        'highlight_text',
        'cta_label',
        'cta_url',
        'image_path',
        'cc_email',
        'status',
        'total_recipient_count',
        'queued_count',
        'sent_count',
        'failed_count',
        'queued_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_recipient_count' => 'integer',
            'queued_count' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
            'queued_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }

    public function templateView(): string
    {
        return 'emails.marketing.'.$this->template_type;
    }

    public function refreshDeliveryStats(): void
    {
        $counts = $this->recipients()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $total = (int) $counts->sum();
        $sent = (int) ($counts['sent'] ?? 0);
        $failed = (int) ($counts['failed'] ?? 0);
        $sending = (int) ($counts['sending'] ?? 0);
        $outstanding = $total - $sent - $failed;

        $status = match (true) {
            $outstanding > 0 && $sending === 0 && $sent === 0 => 'queued',
            $outstanding > 0 => 'processing',
            $failed === 0 => 'completed',
            $sent === 0 => 'failed',
            default => 'partial',
        };

        $this->update([
            'status' => $status,
            'total_recipient_count' => $total,
            'queued_count' => $outstanding,
            'sent_count' => $sent,
            'failed_count' => $failed,
            'completed_at' => $outstanding === 0 ? now() : null,
        ]);
    }
}
