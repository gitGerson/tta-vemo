<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ReminderTelegramNotification;
use Illuminate\Console\Command;

class TestTelegramNotification extends Command
{
    protected $signature = 'telegram:test {user? : Username or id of the recipient (defaults to the first linked user)}';

    protected $description = 'Send a sample reminder to a linked user to verify Telegram delivery end-to-end';

    public function handle(): int
    {
        $identifier = $this->argument('user');

        $user = $identifier
            ? User::where('username', $identifier)->orWhere('id', $identifier)->first()
            : User::whereNotNull('telegram_chat_id')->first();

        if ($user === null) {
            $this->error($identifier
                ? "No user matches \"{$identifier}\"."
                : 'No user has linked Telegram yet.');

            return self::FAILURE;
        }

        if (! $user->isTelegramLinked()) {
            $this->error("User \"{$user->username}\" has not linked Telegram (no chat id).");

            return self::FAILURE;
        }

        $item = [
            'key' => 'test:'.now()->timestamp,
            'type' => 'vehicle.service-due',
            'title' => 'TEST NOTIFICATION',
            'subtitle' => 'Jika Anda menerima pesan ini, integrasi Telegram VEMO berfungsi.',
            'link' => route('home'),
        ];

        // notifyNow runs synchronously (bypasses the queue) so a delivery failure
        // surfaces here instead of in a worker.
        try {
            $user->notifyNow(new ReminderTelegramNotification($item));
        } catch (\Throwable $e) {
            $this->error("Failed to send: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info("Test reminder sent to {$user->name} (chat {$user->telegram_chat_id}).");

        return self::SUCCESS;
    }
}
