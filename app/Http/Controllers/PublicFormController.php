<?php

namespace App\Http\Controllers;

use App\Models\LeadSubmission;
use App\Models\Person;
use App\Models\PublicForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PublicFormController extends Controller
{
    public function index()
    {
        $user = request()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        return Inertia::render('forms/Index', [
            'forms' => PublicForm::withCount('submissions')
                ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:191'],
            'fields'           => ['required', 'array', 'min:1'],
            'success_message'  => ['nullable', 'string'],
            'captcha_required' => ['boolean'],
        ]);
        $data['slug']     = Str::slug($data['name']) . '-' . Str::random(6);
        $data['owner_id'] = $request->user()->id;

        return redirect()->route('forms.index')->with('form', PublicForm::create($data));
    }

    public function update(Request $request, PublicForm $form)
    {
        $this->authorizeFormOwner($request, $form);
        $data = $request->validate([
            'name'             => ['sometimes', 'string', 'max:191'],
            'fields'           => ['sometimes', 'array', 'min:1'],
            'success_message'  => ['nullable', 'string'],
            'captcha_required' => ['boolean'],
            'active'           => ['boolean'],
        ]);
        $form->update($data);
        return back()->with('success', 'Formulário atualizado.');
    }

    public function destroy(PublicForm $form)
    {
        $this->authorizeFormOwner(request(), $form);
        $form->delete();
        return redirect()->route('forms.index')->with('success', 'Formulário removido.');
    }

    public function toggle(PublicForm $form)
    {
        $this->authorizeFormOwner(request(), $form);
        $form->update(['active' => ! $form->active]);
        return back();
    }

    /**
     * Public render of the form (no auth).
     */
    public function show(PublicForm $form)
    {
        abort_unless($form->active, 404);
        return Inertia::render('forms/Public', [
            'form' => $form,
            'hcaptcha_sitekey' => config('services.hcaptcha.sitekey'),
        ]);
    }

    /**
     * Public submission. Creates a lead Person + LeadSubmission.
     */
    public function submit(Request $request, PublicForm $form)
    {
        abort_unless($form->active, 404);

        $rules = collect($form->fields)->mapWithKeys(function ($f) {
            return ["payload.{$f['key']}" => ($f['required'] ?? false) ? ['required'] : ['nullable']];
        })->all();
        if ($form->captcha_required) {
            $rules['captcha_token'] = ['required', 'string'];
        }
        $request->validate($rules);

        if ($form->captcha_required && ! $this->verifyCaptcha($request)) {
            throw ValidationException::withMessages([
                'captcha_token' => 'Falha na verificação anti-bot.',
            ]);
        }

        $payload = $request->input('payload', []);
        $person = null;
        if (! empty($payload['email']) || ! empty($payload['name'])) {
            $person = Person::create([
                'name'  => $payload['name']  ?? 'Lead',
                'email' => $payload['email'] ?? null,
                'phone' => $payload['phone'] ?? null,
                'notes' => "Origem: formulário público \"{$form->name}\"",
            ]);
        }

        LeadSubmission::create([
            'public_form_id' => $form->id,
            'person_id'      => $person?->id,
            'payload'        => $payload,
            'source_ip'      => $request->ip(),
            'user_agent'     => substr((string) $request->userAgent(), 0, 255),
            'submitted_at'   => now(),
        ]);

        return back()->with('success', $form->success_message ?: 'Obrigado!');
    }

    /**
     * Embed snippet (script/iframe) for external sites.
     */
    public function embed(PublicForm $form)
    {
        abort_unless($form->active, 404);
        $url = route('public-forms.show', $form->slug);
        return response("<iframe src=\"{$url}\" style=\"border:0;width:100%;min-height:520px\"></iframe>")
            ->header('Content-Type', 'text/html');
    }

    /**
     * Verify captcha token against hCaptcha. If no secret is configured,
     * fall back to a simple non-empty check so dev environments don't break.
     */
    private function verifyCaptcha(Request $request): bool
    {
        $token  = (string) $request->input('captcha_token');
        $secret = config('services.hcaptcha.secret');

        if (! $secret) {
            return $token !== '';
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://hcaptcha.com/siteverify', [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);
            return (bool) ($response->json('success') ?? false);
        } catch (\Throwable) {
            return false;
        }
    }

    private function authorizeFormOwner(Request $request, PublicForm $form): void
    {
        $user = $request->user();
        if ($form->owner_id !== $user->id && ! $user->hasAnyRole(['admin', 'manager'])) {
            abort(403);
        }
    }
}
