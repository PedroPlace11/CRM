<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CalendarEventAttendee extends Model
{
    protected $fillable = [
        'calendar_event_id', 'attendee_type', 'attendee_id', 'response',
    ];

    public function calendarEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class);
    }

    public function attendee(): MorphTo
    {
        return $this->morphTo();
    }
}
