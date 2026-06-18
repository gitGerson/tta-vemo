<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TelegramLinkController extends Controller
{
    /**
     * Self-service page where a user connects (or disconnects) their Telegram so
     * they can receive reminder push notifications.
     */
    public function show(): View
    {
        $user = Auth::user();
        $username = config('services.telegram_bot.username');

        $deepLink = null;
        if (! $user->isTelegramLinked() && $username) {
            $deepLink = 'https://t.me/'.$username.'?start='.$user->generateTelegramLinkToken();
        }

        return view('account.telegram', [
            'user' => $user,
            'username' => $username,
            'deepLink' => $deepLink,
        ]);
    }

    public function unlink(): RedirectResponse
    {
        Auth::user()->forceFill([
            'telegram_chat_id' => null,
            'telegram_link_token' => null,
            'telegram_linked_at' => null,
        ])->save();

        return redirect()->route('account.telegram')->with('success', 'Telegram berhasil diputuskan.');
    }
}
