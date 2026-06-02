<?php

namespace App\Notifications;

use App\Models\AutomationRule;
use App\Models\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Internal notification fired when an automation rule auto-creates an activity
 * for the deal owner. Delivered via mail + database (in-app inbox).
 */
class AutomationActivityCreated extends Notification
{
    use Queueable;

    public function __construct(
        public AutomationRule $rule,
        public CalendarEvent $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nova atividade automática: {$this->event->title}")
            ->line("A regra \"{$this->rule->name}\" criou uma atividade no seu calendário.")
            ->line($this->event->description ?: '')
            ->action('Abrir calendário', url('/calendar'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'rule_id'          => $this->rule->id,
            'rule_name'        => $this->rule->name,
            'calendar_event_id'=> $this->event->id,
            'title'            => $this->event->title,
            'start_at'         => $this->event->start_at,
        ];
    }
}
