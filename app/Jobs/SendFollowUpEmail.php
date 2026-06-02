<?php

namespace App\Jobs;

use App\Mail\FollowUpMail;
use App\Models\DealEmail;
use App\Models\FollowUpSequence;
use App\Models\FollowUpTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the next follow-up email for a deal sequence and schedules the next one.
 *
 * Behaviour (per spec):
 *  - Sends every 2 working days during business hours (09:00-18:00 weekdays).
 *  - Picks a template not yet used in this sequence.
 *  - Stops when status != active OR deal stage is no longer is_follow_up
 *    OR all templates have been used.
 */
class SendFollowUpEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $sequenceId) {}

    public function handle(): void
    {
        $sequence = FollowUpSequence::with('deal.stage', 'deal.person', 'deal.entity')->find($this->sequenceId);
        if (! $sequence || $sequence->status !== 'active') return;

        $deal = $sequence->deal;
        if (! $deal || ! $deal->stage?->is_follow_up) {
            $sequence->update(['status' => 'stopped', 'stopped_at' => now(), 'stop_reason' => 'stage_changed']);
            return;
        }

        $used = $sequence->used_template_ids ?? [];
        $template = FollowUpTemplate::where('active', true)->whereNotIn('id', $used)->inRandomOrder()->first();
        if (! $template) {
            $sequence->update(['status' => 'completed', 'stopped_at' => now(), 'stop_reason' => 'all_templates_used']);
            return;
        }

        $to = $deal->person?->email ?? $deal->entity?->email;
        if (! $to) {
            $sequence->update(['status' => 'stopped', 'stopped_at' => now(), 'stop_reason' => 'no_recipient']);
            return;
        }

        Mail::to($to)->send(new FollowUpMail($template->subject, $template->body));

        DealEmail::create([
            'deal_id'   => $deal->id,
            'kind'      => 'follow_up',
            'to_email'  => $to,
            'subject'   => $template->subject,
            'body'      => $template->body,
            'sent_at'   => now(),
            'meta'      => ['template_id' => $template->id],
        ]);

        $sequence->update([
            'sent_count'        => $sequence->sent_count + 1,
            'used_template_ids' => array_values(array_unique([...$used, $template->id])),
            'next_send_at'      => $this->nextBusinessSendAt(now()),
        ]);
    }

    private function nextBusinessSendAt(Carbon $from): Carbon
    {
        $next = $from->copy()->addDays(2)->setTime(10, 0);
        while ($next->isWeekend()) {
            $next->addDay();
        }
        return $next;
    }
}
