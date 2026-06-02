<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\GoogleCalendarConnection;
use App\Services\Integrations\GoogleCalendarClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GoogleCalendarController extends Controller
{
    public function index(Request $request)
    {
        $connection = GoogleCalendarConnection::where('user_id', $request->user()->id)->first();

        return Inertia::render('integrations/GoogleCalendar', [
            'connected' => (bool) $connection,
            'sync_enabled' => (bool) ($connection?->sync_enabled ?? false),
            'calendar_id' => $connection?->calendar_id,
            'last_synced_at' => $connection?->last_synced_at,
            'configured' => (bool) (config('services.google.client_id') && config('services.google.client_secret')),
        ]);
    }

    public function connect(Request $request, GoogleCalendarClient $client)
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return back()->withErrors(['google' => 'Google OAuth nao configurado.']);
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        return redirect()->away($client->authUrl($state));
    }

    public function callback(Request $request, GoogleCalendarClient $client)
    {
        $state = $request->query('state');
        if (! $state || $state !== $request->session()->pull('google_oauth_state')) {
            abort(403);
        }

        $code = $request->query('code');
        if (! $code) {
            return redirect()->route('integrations.google')->withErrors(['google' => 'Codigo OAuth em falta.']);
        }

        $data = $client->exchangeCode($code);

        GoogleCalendarConnection::updateOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_at' => now()->addSeconds((int) ($data['expires_in'] ?? 3600)),
            'calendar_id' => 'primary',
            'sync_enabled' => true,
        ]);

        return redirect()->route('integrations.google')->with('success', 'Google Calendar ligado.');
    }

    public function disconnect(Request $request)
    {
        GoogleCalendarConnection::where('user_id', $request->user()->id)->delete();
        return back()->with('success', 'Ligacao removida.');
    }

    public function sync(Request $request, GoogleCalendarClient $client)
    {
        $connection = GoogleCalendarConnection::where('user_id', $request->user()->id)->first();
        if (! $connection) {
            return back()->withErrors(['google' => 'Sem ligacao ativa.']);
        }

        $events = CalendarEvent::query()
            ->where('owner_id', $request->user()->id)
            ->whereBetween('start_at', [now()->subDays(30), now()->addDays(90)])
            ->get();

        foreach ($events as $event) {
            $client->upsertEvent($connection, $event);
        }

        $connection->update(['last_synced_at' => now()]);
        return back()->with('success', 'Sincronizacao concluida.');
    }
}
