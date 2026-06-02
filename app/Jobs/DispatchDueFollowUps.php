<?php

namespace App\Jobs;

use App\Models\FollowUpSequence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Scheduler tick: dispatches SendFollowUpEmail for sequences whose next_send_at <= now()
 * and respects business-hour window (weekday 09:00-18:00 server-local time).
 */
class DispatchDueFollowUps implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = now();
        if ($now->isWeekend() || $now->hour < 9 || $now->hour >= 18) return;

        FollowUpSequence::query()
            ->where('status', 'active')
            ->whereNotNull('next_send_at')
            ->where('next_send_at', '<=', $now)
            ->limit(100)
            ->each(fn ($s) => SendFollowUpEmail::dispatch($s->id));
    }
}
