<?php

namespace App\Console\Commands;

use App\Models\TelegramNotificationLog;
use App\Models\User;
use App\Notifications\ReminderTelegramNotification;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class DispatchTelegramReminders extends Command
{
    protected $signature = 'telegram:dispatch-reminders';

    protected $description = 'Push pending reminder notifications to users who linked Telegram';

    public function handle(NotificationService $notifications): int
    {
        $sent = 0;

        User::whereNotNull('telegram_chat_id')->get()->each(function (User $user) use ($notifications, &$sent): void {
            // CompanyTenantScope and hasPermission both key off the authenticated user,
            // so each recipient must be the active user while their items are resolved.
            Auth::setUser($user);

            foreach ($notifications->pushableItemsForUser($user) as $item) {
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
        });

        Auth::forgetUser();

        $this->info("Dispatched {$sent} Telegram reminder(s).");

        return self::SUCCESS;
    }
}
