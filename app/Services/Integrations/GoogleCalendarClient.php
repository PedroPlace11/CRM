<?php

namespace App\Services\Integrations;

use App\Models\CalendarEvent;
use App\Models\GoogleCalendarConnection;
use App\Models\GoogleCalendarEventMap;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleCalendarClient
{
    public function authUrl(string $state): string
    {
        $base = 'https://accounts.google.com/o/oauth2/v2/auth';
        $params = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'response_type' => 'code',
            'scope' => implode(' ', [
                'https://www.googleapis.com/auth/calendar.events',
                'https://www.googleapis.com/auth/calendar.readonly',
            ]),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return $base . '?' . $params;
    }

    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect'),
            'grant_type' => 'authorization_code',
        ]);

        if (! $response->ok()) {
            throw new \RuntimeException('Falha na troca do token Google.');
        }

        return $response->json();
    }

    public function refreshToken(GoogleCalendarConnection $connection): GoogleCalendarConnection
    {
        if (! $connection->refresh_token) return $connection;

        if ($connection->expires_at && $connection->expires_at->isFuture()) {
            return $connection;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $connection->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (! $response->ok()) {
            throw new \RuntimeException('Falha ao renovar token Google.');
        }

        $data = $response->json();
        $connection->update([
            'access_token' => $data['access_token'],
            'expires_at' => now()->addSeconds((int) ($data['expires_in'] ?? 3600)),
        ]);

        return $connection;
    }

    public function upsertEvent(GoogleCalendarConnection $connection, CalendarEvent $event): string
    {
        $connection = $this->refreshToken($connection);

        $map = GoogleCalendarEventMap::where('calendar_event_id', $event->id)->first();
        $calendarId = $connection->calendar_id ?: 'primary';
        $payload = $this->buildEventPayload($event);

        if ($map) {
            $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events/{$map->google_event_id}";
            $res = Http::withToken($connection->access_token)->put($url, $payload);
            if (! $res->ok()) {
                throw new \RuntimeException('Falha ao atualizar evento no Google Calendar.');
            }
            return $map->google_event_id;
        }

        $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events";
        $res = Http::withToken($connection->access_token)->post($url, $payload);
        if (! $res->ok()) {
            throw new \RuntimeException('Falha ao criar evento no Google Calendar.');
        }

        $googleId = $res->json('id') ?: Str::uuid()->toString();
        GoogleCalendarEventMap::create([
            'connection_id' => $connection->id,
            'calendar_event_id' => $event->id,
            'google_event_id' => $googleId,
        ]);

        return $googleId;
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
