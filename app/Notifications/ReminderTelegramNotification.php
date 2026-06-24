<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class ReminderTelegramNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Emoji prefix per reminder type, falling back to a bell. Keeps one notification
     * class able to render every reminder produced by NotificationService.
     *
     * @var array<string, string>
     */
    private const ICONS = [
        'maintenance.awaiting-confirmation' => '🕔',
        'maintenance.stale-draft' => '⚠️',
        'vehicle.service-due' => '🔧',
        'vehicle.photo-missing' => '📷',
        'vehicle.insurance-overdue' => '🛑',
        'vehicle.insurance-expiring' => '🛡️',
        'vehicle.stnk-due' => '🪪',
        'vehicle.kir-due' => '🧾',
    ];

    /**
     * @param  array<string, mixed>  $item  A single reminder item from NotificationService
     *                                      (key, type, title, subtitle, link).
     */
    public function __construct(private readonly array $item) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $icon = self::ICONS[$this->item['type']] ?? '🔔';

        return TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content($icon.' *'.$this->escape($this->item['title']).'*'.PHP_EOL.$this->escape($this->item['subtitle']))
            ->button('Buka', $this->item['link']);
    }

    /**
     * Escape Telegram Markdown control characters in user-supplied text.
     */
    private function escape(string $text): string
    {
        return str_replace(['_', '*', '`', '['], ['\_', '\*', '\`', '\['], $text);
    }
}
