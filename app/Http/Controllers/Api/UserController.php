<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;           
use Illuminate\Support\Facades\Redis; 
use App\Mail\VerifyEmailMail;        

class UserController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        return $this->successResponse($user, 'Profile retrieved successfully.');
    }

    public function update(Request $request)
    {
        if ($request->has('wa_number')) {
            $wa = preg_replace('/[^0-9]/', '', $request->wa_number);
            
            if (str_starts_with($wa, '0')) {
                $wa = '62' . substr($wa, 1);
            } 
            elseif (str_starts_with($wa, '8')) {
                $wa = '62' . $wa;
            }
            
            $request->merge(['wa_number' => $wa]);
        }

        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'wa_number' => 'sometimes|required|string|unique:users,wa_number,' . $user->id,
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
}