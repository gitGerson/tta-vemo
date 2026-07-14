<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class TelegramUpdateHandler
{
    /**
     * Process a single Telegram "update" array (as returned by getUpdates or a
     * webhook body). Handles the `/start <token>` deep-link message by binding the
     * chat to the user that owns the token. Non-`/start` messages are ignored.
     *
     * @param  array<string, mixed>  $update
     */
    public function handle(array $update): void
    {
        $message = $update['message'] ?? [];
        $text = trim((string) ($message['text'] ?? ''));
        $chatId = $message['chat']['id'] ?? null;

        if ($chatId === null || ! str_starts_with($text, '/start')) {
            return;
        }

        $token = trim(substr($text, strlen('/start')));

        if ($token === '') {
            $this->reply($chatId, 'Buka aplikasi VEMO → menu Connect Telegram untuk menautkan akun Anda.');

            return;
        }

        $user = User::where('telegram_link_token', $token)->first();

        if ($user === null) {
            $this->reply($chatId, 'Tautan tidak valid atau sudah kedaluwarsa. Silakan buat ulang dari aplikasi VEMO.');

            return;
        }

        $user->forceFill([
            'telegram_chat_id' => (string) $chatId,
            'telegram_link_token' => null,
            'telegram_linked_at' => now(),
        ])->save();

        $this->reply($chatId, '✅ Akun *'.$this->escape($user->name).'* berhasil ditautkan. Anda akan menerima notifikasi pengingat di sini.');
    }

    private function reply(int|string $chatId, string $text): void
    {
        $token = config('services.telegram-bot-api.token');

        if (! $token) {
            return;
        }

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }

    /**
     * Escape Telegram Markdown control characters in user-supplied text so names
     * containing `_`/`*`/etc. don't break (or get rejected by) the reply.
     */
    private function escape(string $text): string
    {
        return str_replace(['_', '*', '`', '['], ['\_', '\*', '\`', '\['], $text);
    }
}
