<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (empty($user->email) || empty($user->wa_number)) {
            return response()->json([
                'success' => false,
                'error_code' => 'PROFILE_INCOMPLETE',
                'message' => 'Access denied. Please complete your Email and WhatsApp Number first.'
            ], 403);
        }

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'success' => false,
                'error_code' => 'EMAIL_UNVERIFIED',
                'message' => 'Access denied. Your email has not been verified. Please check your inbox.'
            ], 403);
        }

        if (is_null($user->wa_verified_at)) {
            return response()->json([
                'success' => false,
                'error_code' => 'WA_UNVERIFIED',
                'message' => 'Access denied. Your WhatsApp number has not been verified via OTP.'
            ], 403);
        }

        return $next($request);
    }
}