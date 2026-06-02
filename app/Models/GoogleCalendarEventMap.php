<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleCalendarEventMap extends Model
{
    protected $fillable = [
        'connection_id', 'calendar_event_id', 'google_event_id',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(GoogleCalendarConnection::class, 'connection_id');
    }

    public function calendarEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'calendar_event_id');
    }
}
