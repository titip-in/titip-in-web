<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JastipListing;
use App\Models\JastipRequest;
use App\Models\PrelovedListing;
use App\Models\PrelovedRequest;
use Illuminate\Support\Str;

class BoostController extends Controller
{
    private function processBoost(Request $request, string $modelClass, string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $user = $request->user();

        if ($user->boost_quota <= 0) {
            return $this->errorResponse('You do not have enough boost quota. Please upgrade your tier.', 403);
        }

        $item = $modelClass::find($id);

        if (!$item) {
            return $this->errorResponse('Item not found', 404);
        }

        if ($item->user_id !== $user->id) {
            return $this->errorResponse('Not authorized to boost this item', 403);
        }

        $activeStatus = in_array(class_basename($modelClass), ['JastipListing']) ? 'ACTIVE' : (in_array(class_basename($modelClass), ['PrelovedListing']) ? 'AVAILABLE' : 'OPEN');
        
        if ($item->status !== $activeStatus) {
            return $this->errorResponse("Cannot boost an inactive or closed item.", 400);
        }

        $user->decrement('boost_quota');
        
        $item->update([
            'boosted_at' => now()
        ]);

        return $this->successResponse([
            'remaining_quota' => $user->boost_quota,
            'boosted_at' => $item->boosted_at
        ], 'Item successfully boosted to the top!');
    }

    public function boostJastipListing(Request $request, string $id)
    {
        return $this->processBoost($request, JastipListing::class, $id);
    }

    public function boostJastipRequest(Request $request, string $id)
    {
        return $this->processBoost($request, JastipRequest::class, $id);
    }

    public function boostPrelovedListing(Request $request, string $id)
    {
        return $this->processBoost($request, PrelovedListing::class, $id);
    }

    public function boostPrelovedRequest(Request $request, string $id)
    {
        return $this->processBoost($request, PrelovedRequest::class, $id);
    }
}