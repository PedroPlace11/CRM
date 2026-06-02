<?php

namespace App\Notifications;

use App\Models\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Reminder for a CalendarEvent whose `reminder_at` has just elapsed.
 */
class EventReminder extends Notification
{
    use Queueable;

    public function __construct(public CalendarEvent $event) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Lembrete: {$this->event->title}")
            ->line("Este evento começa a {$this->event->start_at?->format('d/m/Y H:i')}.")
            ->line($this->event->description ?: '')
            ->action('Ver no calendário', url('/calendar'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'calendar_event_id' => $this->event->id,
            'title'             => $this->event->title,
            'start_at'          => $this->event->start_at,
        ];
    }
}
