<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GroqChatService
{
    public function isConfigured(): bool
    {
        return (string) config('services.groq.api_key') !== '';
    }

    /**
     * @param array<int,array{role:string,content:string}> $messages
     */
    public function chat(array $messages, ?string $model = null, ?float $temperature = null, ?int $maxTokens = null): string
    {
        $apiKey = (string) config('services.groq.api_key');
        if ($apiKey === '') {
            throw new RuntimeException('Groq API key is not configured.');
        }

        $payload = [
            'model' => $model ?? (string) config('services.groq.model', 'openai/gpt-oss-120b'),
            'messages' => $messages,
        ];

        if ($temperature !== null) {
            $payload['temperature'] = $temperature;
        }

        if ($maxTokens !== null) {
            $payload['max_tokens'] = $maxTokens;
        }

        $baseUrl = (string) config('services.groq.base_url', 'https://api.groq.com/openai/v1');
        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(60)
            ->post(rtrim($baseUrl, '/') . '/chat/completions', $payload);

        if (! $response->successful()) {
            Log::warning('Groq API error (chat)', [
                'status' => $response->status(),
                'body' => mb_substr((string) $response->body(), 0, 2000),
            ]);
            throw new RuntimeException('Groq API error: ' . $response->status());
        }

        $data = $response->json();
        return (string) ($data['choices'][0]['message']['content'] ?? '');
    }

    /**
     * @param array<int,array{role:string,content:string}> $messages
     * @return iterable<int,string>
     */
    public function stream(array $messages, ?string $model = null, ?float $temperature = null, ?int $maxTokens = null): iterable
    {
        $apiKey = (string) config('services.groq.api_key');
        if ($apiKey === '') {
            throw new RuntimeException('Groq API key is not configured.');
        }

        $payload = [
            'model' => $model ?? (string) config('services.groq.model', 'openai/gpt-oss-120b'),
            'messages' => $messages,
            'stream' => true,
        ];

        if ($temperature !== null) {
            $payload['temperature'] = $temperature;
        }

        if ($maxTokens !== null) {
            $payload['max_tokens'] = $maxTokens;
        }

        $baseUrl = (string) config('services.groq.base_url', 'https://api.groq.com/openai/v1');
        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->withOptions(['stream' => true])
            ->timeout(0)
            ->post(rtrim($baseUrl, '/') . '/chat/completions', $payload);

        if (! $response->successful()) {
            Log::warning('Groq API error (stream)', [
                'status' => $response->status(),
                'body' => mb_substr((string) $response->body(), 0, 2000),
            ]);
            throw new RuntimeException('Groq API error: ' . $response->status());
        }

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (! $body->eof()) {
            $buffer .= $body->read(1024);
            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $pos));
                $buffer = substr($buffer, $pos + 1);

                if ($line === '' || ! str_starts_with($line, 'data:')) {
                    continue;
                }

                $data = trim(substr($line, 5));
                if ($data === '[DONE]') {
                    return;
                }

                $json = json_decode($data, true);
                if (! is_array($json)) {
                    continue;
                }

                $delta = $json['choices'][0]['delta']['content'] ?? '';
                if ($delta !== '') {
                    yield $delta;
                }
            }
        }
    }
}
