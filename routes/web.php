<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\AccessAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AiSuggestionController;
use App\Http\Controllers\AutomationRuleController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\FollowUpTemplateController;
use App\Http\Controllers\Integrations\GoogleCalendarController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProductStatsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\Webhooks\InboundEmailController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome'))->name('home');

// Public form (no auth).
Route::get('/forms/{form:slug}', [PublicFormController::class, 'show'])->name('public-forms.show');
Route::post('/forms/{form:slug}', [PublicFormController::class, 'submit'])->name('public-forms.submit');
Route::get('/forms/{form:slug}/embed', [PublicFormController::class, 'embed'])->name('public-forms.embed');

// Inbound email webhook (e.g. Mailgun/Postmark) — stops follow-up sequence on client reply.
Route::post('/webhooks/email/inbound', [InboundEmailController::class, 'handle'])->name('webhooks.email.inbound');

// 2FA challenge (auth but pre-2FA gate).
Route::middleware('auth')->group(function () {
    Route::get('/2fa/challenge', [TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('/2fa/challenge', [TwoFactorController::class, 'verify'])->name('2fa.verify');
});

Route::middleware(['auth', 'verified', '2fa'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Core CRUD
    Route::resource('entities', EntityController::class);
    Route::resource('people', PersonController::class);
    Route::post('/people/{person}/merge', [PersonController::class, 'merge'])->name('people.merge');
    Route::resource('deals', DealController::class);
    Route::patch('/deals/{deal}/move', [DealController::class, 'move'])->name('deals.move');
    Route::post('/deals/{deal}/follow-up/cancel', [DealController::class, 'cancelFollowUp'])->name('deals.follow-up.cancel');

    Route::get('/calendar', [CalendarEventController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/create', [CalendarEventController::class, 'create'])->name('calendar.create');
    Route::post('/calendar', [CalendarEventController::class, 'store'])->name('calendar.store');
    Route::get('/calendar/{event}', [CalendarEventController::class, 'show'])->name('calendar.show');
    Route::patch('/calendar/{event}', [CalendarEventController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{event}', [CalendarEventController::class, 'destroy'])->name('calendar.destroy');

    // Proposals
    Route::post('/deals/{deal}/proposals', [ProposalController::class, 'store'])->name('proposals.store');
    Route::post('/deals/{deal}/proposals/{proposal}/send', [ProposalController::class, 'send'])->name('proposals.send');
    Route::get('/deals/{deal}/proposals/{proposal}/download', [ProposalController::class, 'download'])->name('proposals.download');
    Route::delete('/deals/{deal}/proposals/{proposal}', [ProposalController::class, 'destroy'])->name('proposals.destroy');

    // Activities (timeline + quick-create)
    Route::post('/deals/{deal}/activities', [ActivityController::class, 'storeForDeal'])->name('deals.activities.store');
    Route::get('/deals/{deal}/timeline', [ActivityController::class, 'timelineForDeal'])->name('deals.timeline');

    // Product stats
    Route::get('/products/stats', [ProductStatsController::class, 'index'])->name('products.stats');
    Route::get('/products/stats/export', [ProductStatsController::class, 'export'])->name('products.stats.export');
    Route::get('/products/{product}', [ProductStatsController::class, 'show'])->name('products.show');

    // Automations
    Route::get('/automations', [AutomationRuleController::class, 'index'])->name('automations.index');
    Route::post('/automations', [AutomationRuleController::class, 'store'])->name('automations.store');
    Route::patch('/automations/{rule}', [AutomationRuleController::class, 'update'])->name('automations.update');
    Route::delete('/automations/{rule}', [AutomationRuleController::class, 'destroy'])->name('automations.destroy');

    // Follow-up templates
    Route::get('/follow-ups/templates', [FollowUpTemplateController::class, 'index'])->name('followups.templates.index');
    Route::post('/follow-ups/templates', [FollowUpTemplateController::class, 'store'])->name('followups.templates.store');
    Route::patch('/follow-ups/templates/{template}', [FollowUpTemplateController::class, 'update'])->name('followups.templates.update');
    Route::delete('/follow-ups/templates/{template}', [FollowUpTemplateController::class, 'destroy'])->name('followups.templates.destroy');

    // Public forms management (auth)
    Route::get('/admin/forms', [PublicFormController::class, 'index'])->name('forms.index');
    Route::post('/admin/forms', [PublicFormController::class, 'store'])->name('forms.store');
    Route::patch('/admin/forms/{form}', [PublicFormController::class, 'update'])->name('forms.update');
    Route::delete('/admin/forms/{form}', [PublicFormController::class, 'destroy'])->name('forms.destroy');
    Route::post('/admin/forms/{form}/toggle', [PublicFormController::class, 'toggle'])->name('forms.toggle');

    // Admin users
    Route::get('/admin/users', [UserAdminController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [UserAdminController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserAdminController::class, 'store'])->name('admin.users.store');

    // Admin access
    Route::get('/admin/access', [AccessAdminController::class, 'index'])->name('admin.access.index');
    Route::post('/admin/access/roles', [AccessAdminController::class, 'storeRole'])->name('admin.access.roles.store');
    Route::delete('/admin/access/roles/{role}', [AccessAdminController::class, 'destroyRole'])->name('admin.access.roles.destroy');
    Route::delete('/admin/access/permissions/{permission}', [AccessAdminController::class, 'destroyPermission'])->name('admin.access.permissions.destroy');

    // AI chat + suggestions
    Route::get('/ai/chat', fn () => Inertia::render('ai/Chat'))->name('ai.chat');
    Route::get('/ai/conversations', [AiChatController::class, 'conversations'])->name('ai.conversations');
    Route::get('/ai/conversations/{conversation}', [AiChatController::class, 'messages'])->name('ai.messages');
    Route::post('/ai/stream', [AiChatController::class, 'stream'])->name('ai.stream');
    Route::get('/ai/suggestions', [AiSuggestionController::class, 'index'])->name('ai.suggestions');
    Route::post('/ai/suggestions/{suggestion}', [AiSuggestionController::class, 'decide'])->name('ai.suggestions.decide');

    // Integrations
    Route::get('/integrations/google', [GoogleCalendarController::class, 'index'])->name('integrations.google');
    Route::get('/integrations/google/connect', [GoogleCalendarController::class, 'connect'])->name('integrations.google.connect');
    Route::get('/integrations/google/callback', [GoogleCalendarController::class, 'callback'])->name('integrations.google.callback');
    Route::post('/integrations/google/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('integrations.google.disconnect');
    Route::post('/integrations/google/sync', [GoogleCalendarController::class, 'sync'])->name('integrations.google.sync');

    // 2FA settings (legacy standalone screen — kept for backward compat)
    Route::get('/settings/2fa', [TwoFactorController::class, 'show'])->name('2fa.show');
    Route::post('/settings/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/settings/2fa/confirm', [TwoFactorController::class, 'confirm'])->name('2fa.confirm');
    Route::delete('/settings/2fa', [TwoFactorController::class, 'disable'])->name('2fa.disable');

    // Unified profile (perfil + password + 2FA com QR code)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/2fa/enable', [ProfileController::class, 'enableTwoFactor'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/confirm', [ProfileController::class, 'confirmTwoFactor'])->name('profile.2fa.confirm');
    Route::delete('/profile/2fa', [ProfileController::class, 'disableTwoFactor'])->name('profile.2fa.disable');
});

// Stub login route so middleware('auth') redirects don't blow up before Breeze is installed.
// Replaced automatically when Breeze publishes routes/auth.php (php artisan breeze:install vue).
if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
} else {
    Route::get('/login', fn () => Inertia::render('Auth/LoginStub'))->name('login');
    Route::post('/login', function (\Illuminate\Http\Request $request) {
        $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! \Illuminate\Support\Facades\Auth::attempt($request->only('email', 'password'), (bool) $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Credenciais inválidas.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    });
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
}
