<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page');
        }

        $user = auth()->user();

        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Insufficient permissions'], 403);
                }

                return redirect()->back()->with('error', 'You do not have permission to perform this action');
            }
        }

        return $next($request);
    }
}
