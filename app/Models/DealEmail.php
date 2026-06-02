<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealEmail extends Model
{
    protected $fillable = [
        'deal_id', 'proposal_id', 'sent_by', 'kind',
        'to_email', 'subject', 'body', 'meta',
        'sent_at', 'opened_at', 'replied_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
