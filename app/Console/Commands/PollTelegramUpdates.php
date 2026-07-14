<?php

namespace App\Console\Commands;

use App\Services\TelegramUpdateHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PollTelegramUpdates extends Command
{
    protected $signature = 'telegram:poll-updates {--once : Fetch one batch and exit (tests/manual)}';

    protected $description = 'Long-poll Telegram getUpdates and process /start linking messages (replaces the webhook)';

    /**
     * Cache key holding the next getUpdates offset. Persisting it (Redis) means
     * updates are not reprocessed across restarts; Telegram retains ~24h as a
     * backstop, and a reprocessed /start is harmless (token already consumed).
     */
    private const OFFSET_KEY = 'telegram:poll:offset';

    /**
     * Seconds Telegram holds the long-poll open before returning an empty batch.
     */
    private const LONG_POLL_TIMEOUT = 30;

    public function handle(TelegramUpdateHandler $handler): int
    {
        $token = config('services.telegram-bot-api.token');

        if (! $token) {
            $this->error('TELEGRAM_BOT_TOKEN is not set.');

            return self::FAILURE;
        }

        $running = true;

        // pcntl signals are only available on POSIX (not Windows/dev); guard so the
        // command still runs with --once where signal handling is irrelevant.
        if (extension_loaded('pcntl')) {
            $this->trap([SIGINT, SIGTERM], function () use (&$running): void {
                $running = false;
            });
        }

        // getUpdates returns 409 while a webhook is registered, so clear any prior
        // webhook before polling.
        Http::post("https://api.telegram.org/bot{$token}/deleteWebhook");

        do {
            $offset = (int) Cache::get(self::OFFSET_KEY, 0);

            try {
                $response = Http::timeout(self::LONG_POLL_TIMEOUT + 10)
                    ->get("https://api.telegram.org/bot{$token}/getUpdates", [
                        'offset' => $offset,
                        'timeout' => self::LONG_POLL_TIMEOUT,
                        'allowed_updates' => ['message'],
                    ]);
            } catch (\Throwable $e) {
                Log::warning('telegram:poll-updates request failed', ['error' => $e->getMessage()]);
                $this->wait();

                continue;
            }

            if (! $response->successful() || $response->json('ok') !== true) {
                Log::warning('telegram:poll-updates non-ok response', ['body' => $response->body()]);
                $this->wait();

                continue;
            }

            $updates = $response->json('result', []);
            $maxUpdateId = null;

            foreach ($updates as $update) {
                $handler->handle($update);
                $maxUpdateId = max($maxUpdateId ?? 0, (int) ($update['update_id'] ?? 0));
            }

            if ($maxUpdateId !== null) {
                Cache::put(self::OFFSET_KEY, $maxUpdateId + 1);
            }
        } while ($running && ! $this->option('once'));

        return self::SUCCESS;
    }

    /**
     * Short back-off after a failed poll so we don't hammer the API.
     */
    private function wait(): void
    {
        if (! $this->option('once')) {
            sleep(5);
        }
    }
}
