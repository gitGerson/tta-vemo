<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramWebhookController extends Controller
{
    /**
     * Receive bot updates from Telegram. Handles the `/start <token>` deep-link
     * message by binding the chat to the user that owns the token. This route is
     * public (Telegram is unauthenticated) and verified via the secret header set
     * on setWebhook.
     */
    public function handle(Request $request): JsonResponse
    {
        $secret = config('services.telegram_bot.webhook_secret');

        if ($secret && ! hash_equals((string) $secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token'))) {
            abort(403);
        }

        $message = $request->input('message', []);
        $text = trim((string) ($message['text'] ?? ''));
        $chatId = $message['chat']['id'] ?? null;

        if ($chatId === null || ! str_starts_with($text, '/start')) {
            return response()->json(['ok' => true]);
        }

        $token = trim(substr($text, strlen('/start')));

        if ($token === '') {
            $this->reply($chatId, 'Buka aplikasi VEMO → menu Connect Telegram untuk menautkan akun Anda.');

            return response()->json(['ok' => true]);
        }

        $user = User::where('telegram_link_token', $token)->first();

        if ($user === null) {
            $this->reply($chatId, 'Tautan tidak valid atau sudah kedaluwarsa. Silakan buat ulang dari aplikasi VEMO.');

            return response()->json(['ok' => true]);
        }

        $user->forceFill([
            'telegram_chat_id' => (string) $chatId,
            'telegram_link_token' => null,
            'telegram_linked_at' => now(),
        ])->save();

        $this->reply($chatId, "✅ Akun *{$user->name}* berhasil ditautkan. Anda akan menerima notifikasi pengingat di sini.");

        return response()->json(['ok' => true]);
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
}
