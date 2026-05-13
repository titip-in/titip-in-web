<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JastipListing;
use App\Models\JastipRequest;
use App\Models\PrelovedListing;
use App\Models\PrelovedRequest;

class UserActivityController extends Controller
{
    public function myJastipListings(Request $request)
    {
        $items = JastipListing::with('category:id,name,icon')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);
        return $this->successResponse($items, 'My Jastip listings retrieved');
    }

    public function myJastipRequests(Request $request)
    {
        $items = JastipRequest::with('category:id,name,icon')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);
        return $this->successResponse($items, 'My Jastip requests retrieved');
    }

    public function myPrelovedListings(Request $request)
    {
        $items = PrelovedListing::with('category:id,name,icon')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);
        return $this->successResponse($items, 'My Preloved listings retrieved');
    }

    public function myPrelovedRequests(Request $request)
    {
        $items = PrelovedRequest::with('category:id,name,icon')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);
        return $this->successResponse($items, 'My Preloved requests retrieved');
    }
}