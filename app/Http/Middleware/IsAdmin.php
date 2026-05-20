<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !($request->user() instanceof Admin)) {
            return response()->json([
                'status' => 403,
                'error' => 'Forbidden',
                'message' => 'Access denied. You are not authorized as an Admin.',
            ], 403);
        }

        return $next($request);
    }
}