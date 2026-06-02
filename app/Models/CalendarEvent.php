<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class CalendarEvent extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title','start_at','end_at','type','priority','completed','owner_id'])
            ->logOnlyDirty()
            ->useLogName('calendar_event');
    }

    protected $fillable = [
        'eventable_type', 'eventable_id', 'owner_id', 'title', 'description',
        'type', 'start_at', 'end_at', 'location', 'priority', 'completed', 'reminder_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'reminder_at' => 'datetime',
        'completed' => 'bool',
    ];

    public function eventable(): MorphTo
    {
        return $this->morphTo();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(CalendarEventAttendee::class);
    }
}
