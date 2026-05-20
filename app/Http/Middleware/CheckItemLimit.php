<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckItemLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->is_banned) {
            return response()->json([
                'status' => 403,
                'error' => 'Forbidden',
                'message' => 'Your account has been suspended. You are not allowed to perform this action.',
            ], 403);
        }

        if (!$user->canAddItem()) {
            return response()->json([
                'status' => 403,
                'error' => 'Limit Reached',
                'message' => "Your " . strtoupper($user->tier->value) . " tier has reached the maximum limit of " . $user->getMaxItemLimit() . " active items. Please upgrade your tier or remove existing items.",
            ], 403);
        }

        return $next($request);
    }
}