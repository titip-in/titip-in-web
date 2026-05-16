<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        
        return $this->successResponse($user, 'Profile retrieved successfully');
    }

    public function update(Request $request)
    {
        if ($request->has('wa_number')) {
            $wa = preg_replace('/[^0-9]/', '', $request->wa_number);
            
            if (str_starts_with($wa, '0')) {
                $wa = '62' . substr($wa, 1);
            } elseif (str_starts_with($wa, '8')) {
                $wa = '62' . $wa;
            }
            
            $request->merge(['wa_number' => $wa]);
        }

        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'wa_number' => 'sometimes|required|string|unique:users,wa_number,' . $user->id,
            'avatar_url' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string|max:100'
        ]);

        $user->update($validated);

        return $this->successResponse($user, 'Profile updated successfully');
    }
}