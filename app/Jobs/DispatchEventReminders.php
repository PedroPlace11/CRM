<?php

namespace App\Jobs;

use App\Models\CalendarEvent;
use App\Notifications\EventReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Scheduler tick: notifies owners of calendar events whose `reminder_at`
 * has just elapsed (and clears it so the same reminder doesn't fire twice).
 */
class DispatchEventReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = now();

        CalendarEvent::query()
            ->whereNotNull('reminder_at')
            ->where('reminder_at', '<=', $now)
            ->where('completed', false)
            ->with('owner')
            ->limit(200)
            ->each(function (CalendarEvent $event) {
                if ($event->owner) {
                    $event->owner->notify(new EventReminder($event));
                }
                $event->update(['reminder_at' => null]);
            });
    }
}
