<?php

namespace App\Console\Commands;

use App\Models\EmailNotificationLog;
use App\Models\TelegramNotificationLog;
use App\Models\User;
use App\Notifications\ReminderEmailNotification;
use App\Notifications\ReminderTelegramNotification;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class DispatchTelegramReminders extends Command
{
    protected $signature = 'telegram:dispatch-reminders';

    protected $description = 'Push pending reminder notifications to users via Telegram and email';

    public function handle(NotificationService $notifications): int
    {
        $telegramSent = 0;
        $emailSent = 0;

        User::query()->get()->each(function (User $user) use ($notifications, &$telegramSent, &$emailSent): void {
            // CompanyTenantScope and hasPermission both key off the authenticated user,
            // so each recipient must be the active user while their items are resolved.
            Auth::setUser($user);

            $items = $notifications->pushableItemsForUser($user);

            if ($items === []) {
                return;
            }

            $telegramSent += $this->dispatchTelegram($user, $items);
            $emailSent += $this->dispatchEmail($user, $items);
        });

        Auth::forgetUser();

        $this->info("Dispatched {$telegramSent} Telegram and {$emailSent} email reminder(s).");

        return self::SUCCESS;
    }

    /**
     * Send one Telegram message per new item to users who linked Telegram.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function dispatchTelegram(User $user, array $items): int
    {
        if ($user->telegram_chat_id === null) {
            return 0;
        }

        $sent = 0;

        foreach ($items as $item) {
            $alreadySent = TelegramNotificationLog::where('user_id', $user->id)
                ->where('notification_key', $item['key'])
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $user->notify(new ReminderTelegramNotification($item));

            TelegramNotificationLog::create([
                'user_id' => $user->id,
                'notification_key' => $item['key'],
                'notification_type' => $item['type'],
                'sent_at' => now(),
            ]);

            $sent++;
        }

        return $sent;
    }

    /**
     * Send a single digest email of all new items to users who have an email address.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function dispatchEmail(User $user, array $items): int
    {
        if ($user->email === null) {
            return 0;
        }

        $alreadySent = EmailNotificationLog::where('user_id', $user->id)
            ->whereIn('notification_key', collect($items)->pluck('key'))
            ->pluck('notification_key')
            ->all();

        $newItems = collect($items)
            ->reject(fn (array $item): bool => in_array($item['key'], $alreadySent, true))
            ->values()
            ->all();

        if ($newItems === []) {
            return 0;
        }

        $user->notify(new ReminderEmailNotification($newItems));

        foreach ($newItems as $item) {
            EmailNotificationLog::create([
                'user_id' => $user->id,
                'notification_key' => $item['key'],
                'notification_type' => $item['type'],
                'sent_at' => now(),
            ]);
        }

        return count($newItems);
    }
}
