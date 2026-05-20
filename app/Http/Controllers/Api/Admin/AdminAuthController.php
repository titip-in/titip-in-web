<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return $this->errorResponse('Invalid admin credentials', 401);
        }

        $token = $admin->createToken('admin_token')->plainTextToken;

        $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'admin' => $admin
        ];

        return $this->successResponse($data, 'Admin login successful');
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return $this->successResponse(null, 'Admin logout successful');
    }
}