<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use App\Mail\VerifyEmailMail;
use App\Mail\ResetPasswordMail;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        if ($request->has('wa_number') && !empty($request->wa_number)) {
            $wa = preg_replace('/[^0-9]/', '', $request->wa_number);
            
            if (str_starts_with($wa, '0')) {
                $wa = '62' . substr($wa, 1);
            } 
            elseif (str_starts_with($wa, '8')) {
                $wa = '62' . $wa;
            }
            
            $request->merge(['wa_number' => $wa]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255', 
            'password' => 'required|string|min:8',
            'wa_number' => 'nullable|string|max:20', 
        ]);

        $existingUser = User::where('email', $validated['email'])->first();

        if ($existingUser) {
            if ($existingUser->email_verified_at !== null) {
                return $this->errorResponse('Email is already registered and verified. Please login.', 422);
            }

            $user = $existingUser;
            $user->tokens()->delete();

            $oldToken = Redis::get("user_email_token:{$user->id}");
            if ($oldToken) {
                Redis::del("email_verify:{$oldToken}");
                Redis::del("user_email_token:{$user->id}");
            }

            if (!empty($validated['wa_number'])) {
                $competitor = User::where('wa_number', $validated['wa_number'])
                                ->where('id', '!=', $user->id)
                                ->first();
                if ($competitor) {
                    if ($competitor->wa_verified_at !== null) {
                        return $this->errorResponse('WhatsApp number is already in use by a verified account.', 422);
                    }
                    $competitor->wa_number = null;
                    $competitor->save();
                }
            }

            $user->update([
                'name' => $validated['name'],
                'password' => Hash::make($validated['password']),
                'wa_number' => $validated['wa_number'] ?? $user->wa_number,
                'auth_provider' => 'local',
                'google_id' => null,
            ]);
            
        } else {
            if (!empty($validated['wa_number'])) {
                $competitor = User::where('wa_number', $validated['wa_number'])->first();
                if ($competitor) {
                    if ($competitor->wa_verified_at !== null) {
                        return $this->errorResponse('WhatsApp number is already in use by a verified account.', 422);
                    }
                    $competitor->wa_number = null;
                    $competitor->save();
                }
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'wa_number' => $validated['wa_number'] ?? null,
                'auth_provider' => 'local',
            ]);
        }

        $token = Str::random(40);
        Redis::setex("email_verify:{$token}", 3600, $user->id);
        Redis::setex("user_email_token:{$user->id}", 3600, $token);

        $verificationUrl = env('FRONTEND_URL') . "/email-verification?token=" . $token;
        Mail::to($user->email)->send(new VerifyEmailMail($user, $verificationUrl));

        $authToken = $user->createToken('auth_token')->plainTextToken;

        $data = [
            'access_token' => $authToken,
            'token_type' => 'Bearer',
            'user' => $user
        ];

        return $this->successResponse($data, 'Registration successful. Please check your email to verify your account.', 201);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $token = $request->token;
        $userId = Redis::get("email_verify:{$token}");

        if (!$userId) {
            return $this->errorResponse('Verification token is invalid or has expired.', 422);
        }

        $user = User::find($userId);
        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if ($user->email_verified_at !== null) {
            Redis::del("email_verify:{$token}");
            Redis::del("user_email_token:{$user->id}");
            return $this->successResponse(null, 'Email is already verified.');
        }

        $user->email_verified_at = now();
        $user->save();

        Redis::del("email_verify:{$token}");
        Redis::del("user_email_token:{$user->id}");

        return $this->successResponse(null, 'Email verified successfully.');
    }

    public function resendEmail(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at !== null) {
            return $this->errorResponse('Email is already verified.', 400);
        }

        $oldToken = Redis::get("user_email_token:{$user->id}");
        if ($oldToken) {
            Redis::del("email_verify:{$oldToken}");
        }

        $token = Str::random(40);
        Redis::setex("email_verify:{$token}", 3600, $user->id);
        Redis::setex("user_email_token:{$user->id}", 3600, $token);

        $verificationUrl = env('FRONTEND_URL') . "/email-verification?token=" . $token;
        Mail::to($user->email)->send(new VerifyEmailMail($user, $verificationUrl));

        return $this->successResponse(null, 'Verification email resent successfully. Please check your inbox.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->auth_provider === 'google' && is_null($user->password)) {
            return $this->errorResponse(
                'This account was registered via Google. Please login with Google, or use "Forgot Password" to set a password for manual login.',
                422
            );
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse('Invalid email or password', 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ];

        return $this->successResponse($data, 'Login successful');
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return $this->successResponse(null, 'Logout successful');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->successResponse(null, 'If your email is registered, you will receive a password reset link.');
        }

        $oldToken = Redis::get("user_password_reset:{$user->id}");
        if ($oldToken) {
            Redis::del("password_reset:{$oldToken}");
        }

        $token = Str::random(40);
        Redis::setex("password_reset:{$token}", 3600, $user->id);
        Redis::setex("user_password_reset:{$user->id}", 3600, $token);

        $resetUrl = env('FRONTEND_URL') . "/reset-password?token=" . $token;
        Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));

        return $this->successResponse(null, 'If your email is registered, you will receive a password reset link.');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8'
        ]);

        $userId = Redis::get("password_reset:{$request->token}");

        if (!$userId) {
            return $this->errorResponse('Reset token is invalid or has expired.', 422);
        }

        $user = User::find($userId);
        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $user->password = Hash::make($request->password);

        if ($user->auth_provider === 'google') {
            $user->auth_provider = 'local';
        }

        $user->save();

        Redis::del("password_reset:{$request->token}");
        Redis::del("user_password_reset:{$user->id}");

        $user->tokens()->delete();

        return $this->successResponse(null, 'Password has been reset successfully. Please login with your new password.');
    }

    public function redirectToGoogle()
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
        $driver = Socialite::driver('google');
        $url = $driver->stateless()->redirect()->getTargetUrl();

        return $this->successResponse(['url' => $url], 'Google OAuth URL generated');
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
            $driver = Socialite::driver('google');
            $googleUser = $driver->stateless()->user();
        } catch (\Exception $e) {
            $frontendLoginUrl = env('FRONTEND_URL') . '/login?error=google_auth_failed';
            return redirect()->away($frontendLoginUrl);
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            $existingUser = User::where('email', $googleUser->getEmail())->first();

            if ($existingUser) {
                if ($existingUser->email_verified_at !== null) {
                    $existingUser->google_id = $googleUser->getId();

                    if (is_null($existingUser->avatar_url)) {
                        $existingUser->avatar_url = $googleUser->getAvatar();
                    }

                    $existingUser->save();
                    $user = $existingUser;

                } else {
                    $existingUser->tokens()->delete();

                    $oldEmailToken = Redis::get("user_email_token:{$existingUser->id}");
                    if ($oldEmailToken) {
                        Redis::del("email_verify:{$oldEmailToken}");
                        Redis::del("user_email_token:{$existingUser->id}");
                    }

                    $existingUser->update([
                        'name' => $googleUser->getName(),
                        'google_id' => $googleUser->getId(),
                        'avatar_url' => $googleUser->getAvatar(),
                        'auth_provider' => 'google',
                        'password' => null,
                        'email_verified_at' => now(),
                    ]);

                    $user = $existingUser;
                }

            } else {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar_url' => $googleUser->getAvatar(),
                    'auth_provider' => 'google',
                    'password' => null,
                    'email_verified_at' => now(),
                ]);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $frontendCallbackUrl = env('FRONTEND_URL') . '/auth/google/callback?token=' . $token;
        
        return redirect()->away($frontendCallbackUrl);
    }
}