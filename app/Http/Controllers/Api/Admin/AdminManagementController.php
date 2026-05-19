<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\JastipListing;
use App\Models\JastipRequest;
use App\Models\PrelovedListing;
use App\Models\PrelovedRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\UserTier;

class AdminManagementController extends Controller
{
    public function getUsers(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'wa_number', 'tier', 'boost_quota', 'is_banned', 'created_at')
            ->latest()
            ->paginate(15);

        return $this->successResponse($users, 'User list retrieved successfully.');
    }

    public function updateUserTier(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $request->validate([
            'tier' => ['required', Rule::enum(UserTier::class)],
        ]);

        $newTier = UserTier::from($request->tier);
        $boostQuota = match($newTier) {
            UserTier::BASIC => 0,
            UserTier::PLUS => 1,
            UserTier::PRO => 5,
        };

        $expirationDate = $newTier === UserTier::BASIC ? null : now()->addMonth();

        $user->update([
            'tier' => $newTier,
            'boost_quota' => $boostQuota,
            'tier_expired_at' => $expirationDate,
        ]);

        return $this->successResponse($user, "User tier successfully updated to {$newTier->value}.");
    }

    public function toggleBanUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $user->is_banned = !$user->is_banned;
        $user->save();

        if ($user->is_banned) {
            $user->tokens()->delete();
        }

        $status = $user->is_banned ? 'banned' : 'unbanned';
        return $this->successResponse(['is_banned' => $user->is_banned], "User account has been successfully {$status}.");
    }

    public function forceDeleteItem(Request $request, $type, $id)
    {
        $validTypes = ['jastip_listing', 'jastip_request', 'preloved_listing', 'preloved_request'];
        if (!in_array($type, $validTypes)) {
            return $this->errorResponse('Invalid item type.', 400);
        }

        $item = match($type) {
            'jastip_listing' => JastipListing::find($id),
            'jastip_request' => JastipRequest::find($id),
            'preloved_listing' => PrelovedListing::find($id),
            'preloved_request' => PrelovedRequest::find($id),
        };

        if (!$item) {
            return $this->errorResponse('Item not found.', 404);
        }

        if (in_array($type, ['jastip_listing', 'preloved_listing'])) {
            $item->images()->delete();
        }

        $item->delete();

        return $this->successResponse(null, "Item ({$type}) successfully force deleted by Admin.");
    }
}