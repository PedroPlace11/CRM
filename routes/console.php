<?php

use App\Jobs\DispatchDueFollowUps;
use App\Jobs\DispatchEventReminders;
use App\Jobs\EvaluateAutomationRules;
use App\Jobs\RunCommercialAgentDaily;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {to}', function (string $to) {
    Mail::raw('Teste de e-mail enviado pelo CRM com mailer '.config('mail.default').'.', function ($message) use ($to) {
        $message->to($to)
            ->subject('Teste de e-mail - CRM');
    });

    $this->info('E-mail de teste enviado para: '.$to);
})->purpose('Send a test email to validate mail configuration');

// Follow-up scheduler (every 30min on weekdays - job itself enforces business hours).
Schedule::job(new DispatchDueFollowUps)->everyThirtyMinutes()->weekdays();

// Calendar event reminders: every 5 minutes.
Schedule::job(new DispatchEventReminders)->everyFiveMinutes();

// Automation rules engine: every hour.
Schedule::job(new EvaluateAutomationRules)->hourly();

// Daily AI commercial agent: every weekday at 07:30.
Schedule::job(new RunCommercialAgentDaily)->weekdays()->dailyAt('07:30');
