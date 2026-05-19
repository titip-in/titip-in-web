<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;           
use Illuminate\Support\Facades\Redis; 
use App\Models\User;
use App\Mail\VerifyEmailMail;
use App\Services\WhatsAppService;  

class UserController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        return $this->successResponse($user, 'Profile retrieved successfully.');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if ($request->has('wa_number') && !empty($request->wa_number)) {
            $wa = preg_replace('/[^0-9]/', '', $request->wa_number);
            
            if (str_starts_with($wa, '0')) {
                $wa = '62' . substr($wa, 1);
            } 
            elseif (str_starts_with($wa, '8')) {
                $wa = '62' . $wa;
            }
            
            $request->merge(['wa_number' => $wa]);

            if ($wa !== $user->wa_number) {
                $competitor = User::where('wa_number', $wa)->first();
                if ($competitor) {
                    if ($competitor->wa_verified_at !== null) {
                        return $this->errorResponse('WhatsApp number is already in use by a verified account.', 422);
                    }
                    $competitor->wa_number = null;
                    $competitor->save();
                }
            }
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'wa_number' => 'sometimes|nullable|string|max:20', 
            'avatar_url' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string|max:100'
        ]);

        $emailChanged = false;

        if (isset($validated['email']) && $user->email !== $validated['email']) {
            $validated['email_verified_at'] = null;
            $emailChanged = true;
        }

        if (isset($validated['wa_number']) && $user->wa_number !== $validated['wa_number']) {
            $validated['wa_verified_at'] = null;
        }

        $user->update($validated);

        if ($emailChanged) {
            $oldToken = Redis::get("user_email_token:{$user->id}");
            if ($oldToken) {
                Redis::del("email_verify:{$oldToken}");
            }

            $token = Str::random(40);
            Redis::setex("email_verify:{$token}", 3600, $user->id);
            Redis::setex("user_email_token:{$user->id}", 3600, $token);

            $verificationUrl = env('FRONTEND_URL') . "/email-verification?token=" . $token;
            Mail::to($user->email)->send(new VerifyEmailMail($user, $verificationUrl));
        }

        return $this->successResponse(
            $user, 
            'Profile updated successfully.' . ($emailChanged ? ' Please check your new email for verification.' : '')
        );
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|different:old_password' 
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return $this->errorResponse('Old password does not match our records.', 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return $this->successResponse(null, 'Password changed successfully.');
    }

    public function requestWaOtp(Request $request, WhatsAppService $waService)
    {
        $user = $request->user();

        if (!$user->wa_number) {
            return $this->errorResponse('WhatsApp number is not set. Please update your profile first.', 400);
        }

        if ($user->wa_verified_at !== null) {
            return $this->errorResponse('WhatsApp number is already verified.', 400);
        }

        $otpCode = (string) random_int(100000, 999999);

        Redis::setex("wa_otp:{$user->id}", 300, $otpCode);

        $sent = $waService->sendOTP($user->wa_number, $otpCode);

        if (!$sent) {
            return $this->errorResponse('Failed to send OTP. Please try again later.', 500);
        }

        return $this->successResponse(null, 'OTP has been sent to your WhatsApp.');
    }

    public function verifyWaOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $user = $request->user();

        if (empty($user->wa_number)) {
            return $this->errorResponse('Your WhatsApp number is empty or has been claimed by another account. Please update your profile.', 400);
        }

        if ($user->wa_verified_at !== null) {
            return $this->errorResponse('WhatsApp number is already verified.', 400);
        }

        $storedOtp = Redis::get("wa_otp:{$user->id}");

        if (!$storedOtp || $storedOtp !== $request->otp) {
            return $this->errorResponse('Invalid or expired OTP.', 400);
        }

        $user->wa_verified_at = now();
        $user->save();

        Redis::del("wa_otp:{$user->id}");

        return $this->successResponse(null, 'WhatsApp number verified successfully.');
    }
}