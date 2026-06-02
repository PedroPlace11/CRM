<?php

namespace App\Services\AI;

use App\Models\AiConversation;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Entity;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Bridge between OpenAI and the CRM data layer.
 *
 * - ask(): single-shot reply (used as streaming fallback).
 * - stream(): yields incremental string chunks.
 *
 * The implementation is intentionally minimal so the rest of the app can be wired up.
 * It uses the openai-php/laravel package when an API key is configured, otherwise
 * falls back to a deterministic local reply built from the CRM data.
 *
 * NOTE: All DB lookups are scoped through the user's policies via $this->scoped*().
 */
class CrmAiAssistant
{
    public function __construct(private GroqChatService $groq)
    {
    }

    /**
     * Attempt a structured CRM query based on the user's message.
     * Returns null when no intent is matched.
     *
     * @return array{reply:string,payload:array}|null
     */
    public function structuredResponse(User $user, string $message): ?array
    {
        $text = $this->normalize($message);

        if ($stageIntent = $this->matchStageMetrics($user, $message, $text)) {
            return $stageIntent;
        }

        if ($personIntent = $this->matchPersonContact($user, $message, $text)) {
            return $personIntent;
        }

        return null;
    }

    public function ask(User $user, AiConversation $conversation, string $message): string
    {
        $context = $this->buildContext($user, $message);

        if ($this->groq->isConfigured()) {
            try {
                return $this->groq->chat(
                    $this->buildMessages($conversation, $message, $context),
                    config('services.groq.model')
                );
            } catch (\Throwable $e) {
                Log::warning('Groq chat failed, falling back.', [
                    'message' => $e->getMessage(),
                ]);
                // fall through to the next provider
            }
        }

        if (! config('openai.api_key')) {
            return $this->localFallback($message, $context);
        }

        try {
            $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
                'model'    => config('services.openai.model', 'gpt-5-nano'),
                'messages' => $this->buildMessages($conversation, $message, $context),
            ]);
            return $response->choices[0]->message->content ?? '';
        } catch (\Throwable $e) {
            return $this->localFallback($message, $context);
        }
    }

    /**
     * @return iterable<int,string>
     */
    public function stream(User $user, AiConversation $conversation, string $message): iterable
    {
        $context = $this->buildContext($user, $message);

        if ($this->groq->isConfigured()) {
            try {
                foreach ($this->groq->stream(
                    $this->buildMessages($conversation, $message, $context),
                    config('services.groq.model')
                ) as $chunk) {
                    yield $chunk;
                }
                return;
            } catch (\Throwable $e) {
                Log::warning('Groq stream failed, falling back.', [
                    'message' => $e->getMessage(),
                ]);
                // fall through to the next provider
            }
        }

        if (! config('openai.api_key')) {
            // simulated chunked stream when no key is configured
            $reply = $this->localFallback($message, $context);
            foreach (str_split($reply, 32) as $chunk) {
                yield $chunk;
            }
            return;
        }

        try {
            $stream = \OpenAI\Laravel\Facades\OpenAI::chat()->createStreamed([
                'model'    => config('services.openai.model', 'gpt-5-nano'),
                'messages' => $this->buildMessages($conversation, $message, $context),
            ]);
            foreach ($stream as $response) {
                $delta = $response->choices[0]->delta->content ?? '';
                if ($delta !== '') yield $delta;
            }
        } catch (\Throwable $e) {
            yield "[fallback] " . $this->localFallback($message, $context);
        }
    }

    private function buildMessages(AiConversation $conversation, string $message, array $context): array
    {
        $history = $conversation->messages()->orderBy('id')->limit(20)->get()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])->all();

        $system = "És um assistente comercial dentro de um CRM. Responde em português, "
            . "de forma curta, baseando-te SEMPRE nos dados fornecidos no contexto. "
            . "Se a informação não estiver no contexto, diz que não tens acesso a esse dado. "
            . "Contexto:\n" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return array_merge([['role' => 'system', 'content' => $system]], $history, [
            ['role' => 'user', 'content' => $message],
        ]);
    }

    /**
     * Pulls a small slice of CRM data the user is allowed to see, used to ground the model.
     * For now: counts + a couple of representative records. Full RAG/SQL-tools come later.
     */
    private function buildContext(User $user, string $message): array
    {
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        $deals   = Deal::query()->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id));
        $people  = Person::query()->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id));
        $entities= Entity::query()->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id));

        return [
            'totals' => [
                'deals'    => (clone $deals)->count(),
                'people'   => (clone $people)->count(),
                'entities' => (clone $entities)->count(),
            ],
            'pipeline_value' => (float) (clone $deals)->sum('value'),
            'top_deals' => (clone $deals)->with('stage:id,name', 'entity:id,name')
                ->orderByDesc('value')->limit(5)
                ->get(['id', 'title', 'value', 'stage_id', 'entity_id'])
                ->toArray(),
        ];
    }

    private function normalize(string $message): string
    {
        $text = mb_strtolower(trim($message));
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        return $text;
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }

    private function scopedDeals(User $user)
    {
        return Deal::query()->when(! $this->isAdmin($user), fn ($q) => $q->where('owner_id', $user->id));
    }

    private function scopedPeople(User $user)
    {
        return Person::query()->when(! $this->isAdmin($user), fn ($q) => $q->where('owner_id', $user->id));
    }

    private function matchStageMetrics(User $user, string $message, string $text): ?array
    {
        if (! preg_match('/estado\s+([^\?\n]+)/i', $message, $m)) return null;

        if (! str_contains($text, 'negocio') && ! str_contains($text, 'negocios') && ! str_contains($text, 'pipeline')) {
            return null;
        }

        $stageTerm = trim($m[1]);
        $stageSlug = Str::slug($stageTerm);

        $stage = DealStage::query()
            ->where('name', 'like', "%{$stageTerm}%")
            ->orWhere('slug', $stageSlug)
            ->first();

        if (! $stage) {
            return [
                'reply' => "Nao encontrei o estado \"{$stageTerm}\".",
                'payload' => [
                    'intent' => 'stage_not_found',
                    'results' => [],
                    'actions' => [],
                ],
            ];
        }

        $deals = $this->scopedDeals($user)->where('stage_id', $stage->id);
        $count = (clone $deals)->count();
        $sum = (float) (clone $deals)->sum('value');
        $top = (clone $deals)
            ->with('entity:id,name')
            ->orderByDesc('value')
            ->limit(5)
            ->get(['id', 'title', 'value', 'entity_id']);

        $results = $top->map(fn ($d) => [
            'type' => 'deal',
            'id' => $d->id,
            'title' => $d->title,
            'value' => (float) $d->value,
            'entity' => $d->entity?->name,
            'url' => "/deals/{$d->id}",
        ])->all();

        $reply = "No estado {$stage->name} tens {$count} negocios com um volume total de € "
            . number_format($sum, 2, ',', '.');

        return [
            'reply' => $reply,
            'payload' => [
                'intent' => 'stage_metrics',
                'stage' => ['id' => $stage->id, 'name' => $stage->name],
                'results' => $results,
                'actions' => $results
                    ? [[
                        'type' => 'open_url',
                        'label' => 'Abrir pipeline',
                        'url' => '/deals',
                    ]]
                    : [],
            ],
        ];
    }

    private function matchPersonContact(User $user, string $message, string $text): ?array
    {
        $phoneMatch = preg_match('/(telemovel|telefone|contacto)\s+do\s+(.+)/i', $message, $m);
        $emailMatch = preg_match('/email\s+do\s+(.+)/i', $message, $mEmail);

        if (! $phoneMatch && ! $emailMatch) return null;

        $name = trim($phoneMatch ? $m[2] : $mEmail[1]);
        $name = trim($name, " \t\n\r\0\x0B?.!");

        $people = $this->scopedPeople($user)
            ->where('name', 'like', "%{$name}%")
            ->limit(5)
            ->get(['id', 'name', 'email', 'phone']);

        if ($people->isEmpty()) {
            return [
                'reply' => "Nao encontrei a pessoa \"{$name}\".",
                'payload' => [
                    'intent' => 'person_not_found',
                    'results' => [],
                    'actions' => [],
                ],
            ];
        }

        $results = $people->map(fn ($p) => [
            'type' => 'person',
            'id' => $p->id,
            'name' => $p->name,
            'email' => $p->email,
            'phone' => $p->phone,
            'url' => "/people/{$p->id}",
        ])->all();

        $first = $people->first();
        $contact = $phoneMatch ? ($first->phone ?: 'sem telefone') : ($first->email ?: 'sem email');

        $reply = $phoneMatch
            ? "O telefone de {$first->name} e {$contact}."
            : "O email de {$first->name} e {$contact}.";

        return [
            'reply' => $reply,
            'payload' => [
                'intent' => $phoneMatch ? 'person_phone' : 'person_email',
                'results' => $results,
                'actions' => [[
                    'type' => 'open_url',
                    'label' => 'Abrir pessoa',
                    'url' => "/people/{$first->id}",
                ]],
            ],
        ];
    }

    private function localFallback(string $message, array $context): string
    {
        return "Assistente AI (modo offline). Sem chave Groq/OpenAI configurada.\n"
            . "Pergunta: {$message}\n"
            . "Contexto resumido: " . json_encode($context['totals']);
    }
}
