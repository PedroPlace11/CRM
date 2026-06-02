<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRun extends Model
{
    protected $fillable = [
        'automation_rule_id', 'deal_id', 'status', 'message', 'ran_at',
    ];

    protected $casts = ['ran_at' => 'datetime'];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'automation_rule_id');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
}
