<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request, NotificationService $notifications): JsonResponse
    {
        $offset = (int) $request->integer('offset', 0);
        // forUserPage() ignores an unknown group, so no validation error is needed
        // here — a stale client just gets the unfiltered page.
        $group = $request->string('group')->value() ?: null;

        return response()->json($notifications->forUserPage($request->user(), $offset, $group));
    }

    public function read(Request $request, NotificationService $notifications): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255'],
        ]);

        $notifications->markRead($request->user(), $validated['key']);

        return response()->json(['status' => 'ok']);
    }
}
