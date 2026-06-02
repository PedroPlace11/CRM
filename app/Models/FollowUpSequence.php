<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUpSequence extends Model
{
    protected $fillable = [
        'deal_id', 'status', 'sent_count', 'used_template_ids',
        'next_send_at', 'stopped_at', 'stop_reason', 'started_at',
    ];

    protected $casts = [
        'used_template_ids' => 'array',
        'next_send_at' => 'datetime',
        'stopped_at' => 'datetime',
        'started_at' => 'datetime',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
}
