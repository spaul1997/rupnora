<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaignRecipient extends Model
{
    protected $fillable = [
        'email_campaign_id',
        'name',
        'email',
        'source',
        'status',
        'attempts',
        'queued_at',
        'sent_at',
        'failed_at',
        'failure_message',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }
}
