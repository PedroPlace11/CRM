<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRule extends Model
{
    protected $fillable = [
        'name', 'active', 'trigger', 'action', 'created_by', 'last_run_at',
    ];

    protected $casts = [
        'active' => 'bool',
        'trigger' => 'array',
        'action' => 'array',
        'last_run_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AutomationRun::class);
    }
}
