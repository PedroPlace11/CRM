<?php

namespace App\Services\AI;

use App\Models\AiSuggestion;
use App\Models\Deal;

/**
 * Daily commercial agent: analyses deals + recent activity and writes
 * AiSuggestion rows for each deal owner.
 *
 * Pipeline:
 *  1. Heuristic baseline produces a candidate suggestion per deal.
 *  2. learningWeight() looks at past accept/dismiss decisions for the same
 *     (owner, action_type) pair so noisy patterns get suppressed over time.
 *  3. If an OpenAI key is configured, askLlmRefinement() can rewrite the
 *     reason/title in plain Portuguese; otherwise the heuristic text is kept.
 */
class CommercialAgent
{
    public function generateDailySuggestions(): void
    {
        $deals = Deal::with('stage', 'owner')->get();

        foreach ($deals as $deal) {
            if (! $deal->owner_id) continue;

            [$reason, $action, $priority] = $this->classify($deal);
            if (! $reason) continue;

            // Learning loop: skip suggestions the user keeps dismissing.
            if ($this->learningWeight($deal->owner_id, $action) < 0.2) {
                continue;
            }

            $refined = $this->askLlmRefinement($deal, $reason, $action);

            AiSuggestion::firstOrCreate([
                'user_id'      => $deal->owner_id,
                'subject_type' => Deal::class,
                'subject_id'   => $deal->id,
                'status'       => 'pending',
            ], [
                'action_type'    => $action,
                'title'          => $refined['title']  ?? "Próximo passo para \"{$deal->title}\"",
                'reason'         => $refined['reason'] ?? $reason,
                'suggested_date' => now()->addDay()->toDateString(),
                'priority'       => $priority,
            ]);
        }
    }

    /**
     * @return array{0:?string,1:?string,2:string} reason, action_type, priority
     */
    private function classify(Deal $deal): array
    {
        if (! $deal->last_activity_at || $deal->last_activity_at->lt(now()->subDays(5))) {
            return ['Negócio sem interação há mais de 5 dias.', 'call', 'high'];
        }
        if ($deal->expected_close_date && $deal->expected_close_date->isPast()
            && ! $deal->stage?->is_won && ! $deal->stage?->is_lost) {
            return ['Data prevista de fecho ultrapassada.', 'meeting', 'high'];
        }
        if ($deal->probability >= 70) {
            return ['Negócio com alta probabilidade. Confirmar próximos passos.', 'follow_up', 'normal'];
        }
        return [null, null, 'normal'];
    }

    /**
     * Learning signal: ratio of accepted vs dismissed past suggestions for this
     * (user, action_type). Returns 1.0 when there is no history (full trust).
     */
    private function learningWeight(int $userId, ?string $action): float
    {
        if (! $action) return 1.0;
        $base = AiSuggestion::where('user_id', $userId)
            ->where('action_type', $action)
            ->whereIn('status', ['accepted', 'dismissed']);
        $total = (clone $base)->count();
        if ($total < 5) return 1.0;
        $accepted = (clone $base)->where('status', 'accepted')->count();
        return max(0.0, min(1.0, $accepted / $total));
    }

    /**
     * Ask the LLM to rewrite reason/title with deal context. Silent fallback if no key.
     *
     * @return array{title?:string,reason?:string}
     */
    private function askLlmRefinement(Deal $deal, string $reason, ?string $action): array
    {
        if (! config('openai.api_key')) return [];

        try {
            $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
                'model'    => config('services.openai.model', 'gpt-5-nano'),
                'messages' => [
                    ['role' => 'system', 'content' =>
                        "És um agente comercial. Recebe um negócio e uma razão técnica e devolve um JSON ".
                        "com chaves \"title\" (curto, accionável) e \"reason\" (1 frase em PT)."],
                    ['role' => 'user', 'content' => json_encode([
                        'deal'    => [
                            'title'   => $deal->title,
                            'value'   => $deal->value,
                            'stage'   => $deal->stage?->name,
                            'days_inactive' => $deal->last_activity_at?->diffInDays(now()),
                        ],
                        'action'  => $action,
                        'reason'  => $reason,
                    ], JSON_UNESCAPED_UNICODE)],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);
            $raw = $response->choices[0]->message->content ?? '{}';
            $data = json_decode($raw, true) ?: [];
            return array_intersect_key($data, ['title' => 1, 'reason' => 1]);
        } catch (\Throwable) {
            return [];
        }
    }
}
