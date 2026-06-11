<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {url? : Full webhook URL (defaults to APP_URL/telegram/webhook)}';

    protected $description = 'Register the Telegram bot webhook with its secret token';

    public function handle(): int
    {
        $token = config('services.telegram-bot-api.token');

        if (! $token) {
            $this->error('TELEGRAM_BOT_TOKEN is not set.');

            return self::FAILURE;
        }

        $url = $this->argument('url') ?: rtrim((string) config('app.url'), '/').'/telegram/webhook';

        $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", array_filter([
            'url' => $url,
            'secret_token' => config('services.telegram_bot.webhook_secret'),
        ]));

        if ($response->successful() && $response->json('ok')) {
            $this->info("Webhook registered: {$url}");

            return self::SUCCESS;
        }

        $this->error('Failed to set webhook: '.$response->body());

        return self::FAILURE;
    }
}
