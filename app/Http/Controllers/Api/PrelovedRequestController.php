<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrelovedRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PrelovedRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = PrelovedRequest::with([
            'user:id,name,wa_number,avatar_url,status',
            'category:id,name,icon'
        ])
        ->where('status', 'OPEN') 
        ->orderByRaw('COALESCE(boosted_at, created_at) DESC')
        ->get();

        return $this->successResponse($items, 'Preloved request catalog retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_price' => 'nullable|integer|min:0',
            'status' => 'nullable|in:OPEN,FOUND,CLOSED'
        ]);

        $validated['user_id'] = $request->user()->id;

        try {
            $categoryName = Category::find($validated['category_id'])?->name ?? '';
            $textToEmbed = trim($categoryName . ' - Dicari: ' . $validated['title'] . ' ' . ($validated['description'] ?? ''));
            
            $embeddingArray = Str::of($textToEmbed)->toEmbeddings();
            $validated['embedding'] = '[' . implode(',', $embeddingArray) . ']';
        } catch (\Throwable $e) {
            Log::error('Failed to generate embedding for Preloved Request: ' . $e->getMessage());
        }

        $reqItem = PrelovedRequest::create($validated);

        $reqItem->load([
            'user:id,name,wa_number,avatar_url,status',
            'category:id,name'
        ]);

        return $this->successResponse($reqItem, 'Preloved request posted successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $reqItem = PrelovedRequest::with([
            'user:id,name,wa_number,avatar_url,status',
            'category:id,name,icon'
        ])->find($id);

        if (!$reqItem) {
            return $this->errorResponse('Preloved request not found', 404);
        }

        if ($reqItem->status !== 'OPEN' && $reqItem->user_id !== auth('sanctum')->id()) {
            return $this->errorResponse('This Preloved request is not open and cannot be viewed by the public.', 403);
        }

        $redisKey = "views:preloved_request:{$id}";
        Redis::incr($redisKey);

        return $this->successResponse($reqItem, 'Preloved request detail retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $reqItem = PrelovedRequest::find($id);
        
        if (!$reqItem) {
            return $this->errorResponse('Preloved request not found', 404);
        }

        if ($reqItem->user_id !== $request->user()->id) {
            return $this->errorResponse('Not authorized to modify this item', 403);
        }

        $isReactivating = $reqItem->status !== 'OPEN' && $request->input('status') === 'OPEN';
        if ($isReactivating) {
            if (!$request->user()->canAddItem('preloved_request')) {
                $maxLimit = $request->user()->getMaxItemLimit();
                $tierName = strtoupper($request->user()->tier->value);
                return $this->errorResponse("Failed to reactivate. Your {$tierName} tier has reached the maximum limit of {$maxLimit} active items.", 400);
            }
            $reqItem->created_at = now();
            $reqItem->boosted_at = null;
        }

        $isClosing = $reqItem->status === 'OPEN' && $request->input('status') !== 'OPEN';
        if ($isClosing) {
            $reqItem->boosted_at = null;
        }

        $validated = $request->validate([
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'max_price' => 'sometimes|nullable|integer|min:0',
            'status' => 'sometimes|nullable|in:OPEN,FOUND,CLOSED'
        ]);

        if (isset($validated['title']) || isset($validated['description']) || isset($validated['category_id'])) {
            try {
                $newTitle = $validated['title'] ?? $reqItem->title;
                $newDesc = $validated['description'] ?? $reqItem->description;
                $newCatId = $validated['category_id'] ?? $reqItem->category_id;
                
                $categoryName = Category::find($newCatId)?->name ?? '';
                $textToEmbed = trim($categoryName . ' - Dicari: ' . $newTitle . ' ' . $newDesc);
                
                $embeddingArray = Str::of($textToEmbed)->toEmbeddings();
                $validated['embedding'] = '[' . implode(',', $embeddingArray) . ']';
            } catch (\Throwable $e) {
                Log::error('Failed to update embedding for Preloved Request: ' . $e->getMessage());
            }
        }

        $reqItem->update($validated);

        $reqItem->load([
            'user:id,name,wa_number,avatar_url,status',
            'category:id,name'
        ]);

        return $this->successResponse($reqItem, 'Preloved request updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $reqItem = PrelovedRequest::find($id);
        
        if (!$reqItem) {
            return $this->errorResponse('Preloved request not found', 404);
        }

        if ($reqItem->user_id !== $request->user()->id) {
            return $this->errorResponse('Not authorized to modify this item', 403);
        }

        $reqItem->delete();

        return $this->successResponse(null, 'Preloved request deleted successfully');
    }
}
