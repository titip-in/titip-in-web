<?php

namespace App\Http\Controllers\Api;

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
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'wa_number' => 'sometimes|required|string|unique:users,wa_number,' . $user->id,
            'avatar_url' => 'sometimes|nullable|string'
        ]);

        $user->update($validated);

        return $this->successResponse($user, 'Profile updated successfully');
    }
}