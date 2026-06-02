<?php

namespace App\Services\Integrations;

use App\Models\CalendarEvent;
use App\Models\GoogleServiceAccountEventMap;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleServiceAccountCalendarClient
{
    public function isConfigured(): bool
    {
        return (bool) $this->serviceAccountJsonPath();
    }

    public function upsertEvent(CalendarEvent $event): string
    {
        $calendarId = $this->calendarId();
        $payload = $this->buildEventPayload($event);
        $map = GoogleServiceAccountEventMap::where('calendar_event_id', $event->id)->first();

        if ($map) {
            $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/{$map->google_event_id}";
            $res = Http::withToken($this->accessToken())->put($url, $payload);
            if (! $res->ok()) {
                throw new \RuntimeException('Falha ao atualizar evento no Google Calendar (service account).');
            }
            return $map->google_event_id;
        }

        $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events";
        $res = Http::withToken($this->accessToken())->post($url, $payload);
        if (! $res->ok()) {
            throw new \RuntimeException('Falha ao criar evento no Google Calendar (service account).');
        }

        $googleId = $res->json('id') ?: Str::uuid()->toString();
        GoogleServiceAccountEventMap::create([
            'calendar_event_id' => $event->id,
            'google_event_id' => $googleId,
        ]);

        return $googleId;
    }

    public function deleteEvent(CalendarEvent $event): void
    {
        $map = GoogleServiceAccountEventMap::where('calendar_event_id', $event->id)->first();
        if (! $map) {
            return;
        }

        $calendarId = $this->calendarId();
        $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/{$map->google_event_id}";
        $res = Http::withToken($this->accessToken())->delete($url);

        if ($res->status() !== 204 && $res->status() !== 404) {
            throw new \RuntimeException('Falha ao remover evento no Google Calendar (service account).');
        }

        $map->delete();
    }

    private function accessToken(): string
    {
        $cacheKey = 'google_service_account_access_token';
        return Cache::remember($cacheKey, now()->addMinutes(55), function () {
            $jwt = $this->buildJwt();
            $res = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (! $res->ok()) {
                throw new \RuntimeException('Falha ao obter token do Google (service account).');
            }

            return (string) $res->json('access_token');
        });
    }

    private function buildJwt(): string
    {
        $serviceAccount = $this->serviceAccountJson();
        $now = time();

        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ]));

        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $serviceAccount['client_email'],
            'scope' => implode(' ', $this->scopes()),
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ]));

        $signatureInput = $header . '.' . $payload;
        $signature = '';
        $privateKey = $serviceAccount['private_key'] ?? '';
        $key = openssl_pkey_get_private($privateKey);

        if (! $key) {
            throw new \RuntimeException('Chave privada invalida no service account JSON.');
        }

        openssl_sign($signatureInput, $signature, $key, OPENSSL_ALGO_SHA256);

        return $signatureInput . '.' . $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function scopes(): array
    {
        $scopes = config('services.google_service_account.scopes');
        if (is_string($scopes)) {
            $scopes = preg_split('/[\s,]+/', $scopes, -1, PREG_SPLIT_NO_EMPTY);
        }

        return $scopes ?: ['https://www.googleapis.com/auth/calendar'];
    }

    private function calendarId(): string
    {
        return (string) config('services.google_service_account.calendar_id', 'primary');
    }

    private function serviceAccountJsonPath(): ?string
    {
        $path = config('services.google_service_account.json');
        if (! $path) {
            return null;
        }

        return is_string($path) && file_exists($path) ? $path : null;
    }

    private function serviceAccountJson(): array
    {
        $path = $this->serviceAccountJsonPath();
        if (! $path) {
            throw new \RuntimeException('Service account JSON nao configurado.');
        }

        $contents = file_get_contents($path);
        $json = json_decode($contents ?: '', true);

        if (! is_array($json)) {
            throw new \RuntimeException('Service account JSON invalido.');
        }

        return $json;
    }

    private function buildEventPayload(CalendarEvent $event): array
    {
        return [
            'summary' => $event->title,
            'description' => $event->description,
            'location' => $event->location,
            'start' => [
                'dateTime' => $event->start_at->toIso8601String(),
            ],
            'end' => [
                'dateTime' => ($event->end_at ?: $event->start_at->copy()->addMinutes(30))->toIso8601String(),
            ],
        ];
    }
}
