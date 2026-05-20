<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class AnalyticsController extends Controller
{
    public function trackClick(string $type, string $id)
    {
        $validTypes = ['jastip_listing', 'jastip_request', 'preloved_listing', 'preloved_request'];
        
        if (!in_array($type, $validTypes)) {
            return $this->errorResponse('Invalid item type', 400);
        }

        $redisKey = "clicks:{$type}:{$id}";
        Redis::incr($redisKey);

        return $this->successResponse(null, 'Click tracked successfully');
    }

    public function getDashboardAnalytics(Request $request)
    {
        $user = $request->user();
        $tier = strtoupper($user->tier->value);

        if ($tier === 'BASIC') {
            return $this->errorResponse('Analytics is only available for Plus and Pro tiers. Please upgrade your account to unlock this feature.', 403);
        }

        $relations = [
            'jastipListings' => 'jastip_listing',
            'jastipRequests' => 'jastip_request',
            'prelovedListings' => 'preloved_listing',
            'prelovedRequests' => 'preloved_request'
        ];

        $allItems = collect();
        foreach ($relations as $relation => $type) {
            $items = $user->$relation()->select('id', 'title')->get()->map(function ($item) use ($type) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'type' => $type
                ];
            });
            $allItems = $allItems->concat($items);
        }

        $totalViews = 0;
        $totalClicks = 0;
        $itemDetails = [];
        $bestItem = null;
        $maxInteraction = -1;

        foreach ($allItems as $item) {
            $views = (int) Redis::get("views:{$item['type']}:{$item['id']}") ?: 0;
            $clicks = (int) Redis::get("clicks:{$item['type']}:{$item['id']}") ?: 0;

            $totalViews += $views;
            $totalClicks += $clicks;

            $itemDetails[] = [
                'id' => $item['id'],
                'title' => $item['title'],
                'type' => $item['type'],
                'views' => $views,
                'clicks' => $clicks
            ];

            if ($tier === 'PRO') {
                $interaction = $views + $clicks;
                if ($interaction > $maxInteraction && $interaction > 0) {
                    $maxInteraction = $interaction;
                    $bestItem = end($itemDetails);
                }
            }
        }

        $data = [
            'total_views' => $totalViews,
            'total_clicks' => $totalClicks,
            'item_details' => $itemDetails,
        ];

        if ($tier === 'PRO') {
            $conversionRate = $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 2) : 0;
            $data['conversion_rate'] = $conversionRate;
            $data['best_item'] = $bestItem;
        }

        return $this->successResponse($data, 'Analytics retrieved successfully');
    }
}