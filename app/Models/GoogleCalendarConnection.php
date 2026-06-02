<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoogleCalendarConnection extends Model
{
    protected $fillable = [
        'user_id', 'access_token', 'refresh_token',
        'expires_at', 'calendar_id', 'sync_enabled',
        'last_synced_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'sync_enabled' => 'bool',
        'last_synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventMaps(): HasMany
    {
        return $this->hasMany(GoogleCalendarEventMap::class, 'connection_id');
    }
}
