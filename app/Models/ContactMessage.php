<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = [
        'ticket_no', 'name', 'email', 'phone', 'subject', 'message',
        'priority', 'status', 'assigned_to', 'admin_note', 'admin_reply', 'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public static function generateTicketNumber(): string
    {
        $year = now()->year;
        $prefix = "CNT-{$year}-";

        $lastNumber = static::where('ticket_no', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('ticket_no');

        $sequence = $lastNumber ? ((int) substr($lastNumber, -5)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
