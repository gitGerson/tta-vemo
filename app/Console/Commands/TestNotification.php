<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ReminderEmailNotification;
use App\Notifications\ReminderTelegramNotification;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    protected $signature = 'notifications:test
        {user? : Username or id of the recipient (defaults to the first user with email or linked Telegram)}
        {--email : Send only the email channel}
        {--telegram : Send only the Telegram channel}';

    protected $description = 'Send a sample reminder to a user via email and/or Telegram to verify delivery end-to-end';

    public function handle(): int
    {
        $user = $this->resolveUser();

        if ($user === null) {
            return self::FAILURE;
        }

        // With no channel flag, attempt every channel the user is reachable on.
        $email = $this->option('email') || ! $this->option('telegram');
        $telegram = $this->option('telegram') || ! $this->option('email');

        $item = [
            'key' => 'test:'.now()->timestamp,
            'type' => 'vehicle.stnk-due',
            'title' => 'TEST NOTIFICATION',
            'subtitle' => 'Jika Anda menerima pesan ini, pengiriman notifikasi VEMO berfungsi.',
            'link' => route('home'),
        ];

        $sent = 0;
        $sent += $email && $this->sendEmail($user, $item) ? 1 : 0;
        $sent += $telegram && $this->sendTelegram($user, $item) ? 1 : 0;

        if ($sent === 0) {
            $this->error('No channel was sent — check the warnings above.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function resolveUser(): ?User
    {
        $identifier = $this->argument('user');

        $user = $identifier
            ? User::where('username', $identifier)->orWhere('id', $identifier)->first()
            : User::where(fn ($query) => $query->whereNotNull('email')->orWhereNotNull('telegram_chat_id'))->first();

        if ($user === null) {
            $this->error($identifier
                ? "No user matches \"{$identifier}\"."
                : 'No user has an email address or linked Telegram yet.');
        }

        return $user;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function sendEmail(User $user, array $item): bool
    {
        if ($user->email === null) {
            $this->warn("Email skipped: \"{$user->username}\" has no email address.");

            return false;
        }

        // notifyNow runs synchronously (bypasses the queue) so a delivery failure
        // surfaces here instead of in a worker.
        try {
            $user->notifyNow(new ReminderEmailNotification([$item]));
        } catch (\Throwable $e) {
            $this->error("Email failed: {$e->getMessage()}");

            return false;
        }

        $this->info("Test email sent to {$user->name} <{$user->email}>.");

        return true;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function sendTelegram(User $user, array $item): bool
    {
        if (! $user->isTelegramLinked()) {
            $this->warn("Telegram skipped: \"{$user->username}\" has not linked Telegram (no chat id).");

            return false;
        }

        try {
            $user->notifyNow(new ReminderTelegramNotification($item));
        } catch (\Throwable $e) {
            $this->error("Telegram failed: {$e->getMessage()}");

            return false;
        }

        $this->info("Test reminder sent to {$user->name} (chat {$user->telegram_chat_id}).");

        return true;
    }
}
