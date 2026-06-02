<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiSuggestion extends Model
{
    protected $fillable = [
        'user_id', 'subject_type', 'subject_id', 'action_type',
        'title', 'reason', 'suggested_date', 'priority', 'status', 'decided_at',
    ];

    protected $casts = [
        'suggested_date' => 'date',
        'decided_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
