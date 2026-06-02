<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\DealEmail;
use App\Models\FollowUpSequence;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Inbound email webhook (Mailgun/Postmark/SES style).
 *
 * Expected payload (kept provider-agnostic — pick first non-empty key):
 *   - from     : sender email
 *   - subject  : subject line
 *   - body     : plain-text body (optional)
 *   - in_reply_to / references : RFC headers used to correlate to a previous DealEmail.
 *
 * Behaviour:
 *   - If the sender matches a Deal.person/entity OR a previous DealEmail recipient,
 *     we mark the most recent DealEmail as replied, stop the active FollowUpSequence
 *     and write an Activity row in the deal's timeline.
 *
 * Auth: shared-secret query param `?token=` matched against env('INBOUND_EMAIL_TOKEN').
 */
class InboundEmailController extends Controller
{
    public function handle(Request $request)
    {
        $expected = env('INBOUND_EMAIL_TOKEN');
        if ($expected && $request->query('token') !== $expected) {
            abort(401);
        }

        $from        = strtolower(trim((string) ($request->input('from') ?? $request->input('sender') ?? '')));
        $subject     = (string) ($request->input('subject') ?? '');
        $body        = (string) ($request->input('body-plain') ?? $request->input('body') ?? $request->input('TextBody') ?? '');
        $inReplyTo   = (string) ($request->input('in_reply_to') ?? $request->input('In-Reply-To') ?? '');

        if (! $from) {
            return response()->json(['ignored' => 'missing from'], 202);
        }

        // Pull the email out of "Name <email@x>".
        if (preg_match('/<([^>]+)>/', $from, $m)) $from = strtolower($m[1]);

        $deal = $this->resolveDeal($from, $inReplyTo);
        if (! $deal) {
            Log::info('[InboundEmail] no deal match', compact('from', 'subject'));
            return response()->json(['ignored' => 'no deal']);
        }

        DealEmail::where('deal_id', $deal->id)
            ->where('to_email', $from)
            ->whereNull('replied_at')
            ->latest('sent_at')
            ->limit(1)
            ->update(['replied_at' => now()]);

        FollowUpSequence::where('deal_id', $deal->id)
            ->where('status', 'active')
            ->update([
                'status'      => 'stopped',
                'stopped_at'  => now(),
                'stop_reason' => 'client_replied',
            ]);

        Activity::create([
            'subject_type' => Deal::class,
            'subject_id'   => $deal->id,
            'user_id'      => $deal->owner_id,
            'type'         => 'email',
            'title'        => "Resposta do cliente: " . ($subject ?: 'sem assunto'),
            'body'         => mb_substr($body, 0, 4000),
            'happened_at'  => now(),
            'meta'         => ['from' => $from, 'in_reply_to' => $inReplyTo],
        ]);

        return response()->json(['ok' => true, 'deal_id' => $deal->id]);
    }

    private function resolveDeal(string $from, string $inReplyTo): ?Deal
    {
        if ($inReplyTo) {
            $email = DealEmail::where('meta->message_id', $inReplyTo)->latest()->first();
            if ($email) return Deal::find($email->deal_id);
        }

        $email = DealEmail::where('to_email', $from)->latest('sent_at')->first();
        if ($email) return Deal::find($email->deal_id);

        $personDeal = Person::where('email', $from)
            ->whereHas('deals')
            ->with(['deals' => fn ($q) => $q->latest('updated_at')->limit(1)])
            ->first();
        if ($personDeal && $personDeal->deals->isNotEmpty()) {
            return $personDeal->deals->first();
        }

        return null;
    }
}
