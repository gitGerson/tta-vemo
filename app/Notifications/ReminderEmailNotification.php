<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, array<string, mixed>>  $items  The new (not-yet-emailed) reminder items
     *                                                   for one user, from NotificationService.
     */
    public function __construct(private readonly array $items) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = count($this->items);

        return (new MailMessage)
            ->subject('Pengingat VEMO · '.$count.' item perlu perhatian')
            ->markdown('emails.reminder-digest', [
                'greetingName' => $notifiable->name,
                'items' => $this->items,
            ]);
    }
}
