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

        return response()->json($notifications->forUserPage($request->user(), $offset));
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
