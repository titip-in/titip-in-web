<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrelovedListing;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PrelovedListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = PrelovedListing::with([
            'user:id,name,wa_number,avatar_url,status',
            'category:id,name,icon',
            'images'
        ])
        ->where('status', 'AVAILABLE') 
        ->latest()->get();
        
        return $this->successResponse($items, 'Preloved listing catalog retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $maxImages = ($request->user()->tier === \App\Enums\UserTier::PRO) ? 6 : 3;

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'condition' => 'required|in:NEW,LIKE_NEW,GOOD,FAIR',
            'images' => 'required|array|min:1|max:' . $maxImages,
            'images.*' => 'required|url',
            'primary_image_url' => 'nullable|url',
            'status' => 'nullable|in:AVAILABLE,SOLD,CLOSED'
        ], [
            'images.max' => "Your " . strtoupper($request->user()->tier->value) . " tier only allows a maximum of {$maxImages} images."
        ]);

        $images = $request->input('images', []);
        $primaryUrl = $request->input('primary_image_url', $images[0] ?? null);
        unset($validated['images'], $validated['primary_image_url']);

        $validated['user_id'] = $request->user()->id;

        try {
            $categoryName = Category::find($validated['category_id'])?->name ?? '';
            $textToEmbed = trim($categoryName . ' - ' . $validated['title'] . ' ' . ($validated['description'] ?? ''));
            
            $embeddingArray = Str::of($textToEmbed)->toEmbeddings();
            $validated['embedding'] = '[' . implode(',', $embeddingArray) . ']';
        } catch (\Throwable $e) {
            Log::error('Failed to generate embedding for Preloved: ' . $e->getMessage());
        }

        $listing = PrelovedListing::create($validated);

        foreach ($images as $url) {
            $listing->images()->create([
                'image_url' => $url,
                'is_primary' => $url === $primaryUrl,
            ]);
        }

        $listing->load([
            'user:id,name,wa_number,avatar_url,status', 
            'category:id,name',
            'images'
        ]);

        return $this->successResponse($listing, 'Preloved listing posted successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $listing = PrelovedListing::with([
            'user:id,name,wa_number,avatar_url,status',
            'category:id,name,icon',
            'images'
        ])->find($id);

        if (!$listing) {
            return $this->errorResponse('Preloved listing not found', 404);
        }

        if ($listing->status !== 'AVAILABLE' && $listing->user_id !== auth('sanctum')->id()) {
            return $this->errorResponse('This Preloved listing is not available and cannot be viewed by the public.', 403);
        }

        return $this->successResponse($listing, 'Preloved listing detail retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $listing = PrelovedListing::find($id);
        
        if (!$listing) {
            return $this->errorResponse('Preloved listing not found', 404);
        }

        if ($listing->user_id !== $request->user()->id) {
            return $this->errorResponse('Not authorized to modify this item', 403);
        }

        $isReactivating = $listing->status !== 'AVAILABLE' && $request->input('status') === 'AVAILABLE';
        if ($isReactivating) {
            if (!$request->user()->canAddItem()) {
                $maxLimit = $request->user()->getMaxItemLimit();
                $tierName = strtoupper($request->user()->tier->value);
                return $this->errorResponse("Failed to reactivate. Your {$tierName} tier has reached the maximum limit of {$maxLimit} active items.", 400);
            }
            $listing->created_at = now();
        }

        $maxImages = ($request->user()->tier === \App\Enums\UserTier::PRO) ? 6 : 3;

        $validated = $request->validate([
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|integer|min:0',
            'condition' => 'sometimes|required|in:NEW,LIKE_NEW,GOOD,FAIR',
            'images' => 'sometimes|required|array|min:1|max:' . $maxImages,
            'images.*' => 'required|url',
            'primary_image_url' => 'sometimes|nullable|url',
            'status' => 'sometimes|nullable|in:AVAILABLE,SOLD,CLOSED'
        ], [
            'images.max' => "Your " . strtoupper($request->user()->tier->value) . " tier only allows a maximum of {$maxImages} images."
        ]);

        if (isset($validated['title']) || isset($validated['description']) || isset($validated['category_id'])) {
            try {
                $newTitle = $validated['title'] ?? $listing->title;
                $newDesc = $validated['description'] ?? $listing->description;
                $newCatId = $validated['category_id'] ?? $listing->category_id;
                
                $categoryName = Category::find($newCatId)?->name ?? '';
                $textToEmbed = trim($categoryName . ' - ' . $newTitle . ' ' . $newDesc);
                
                $embeddingArray = Str::of($textToEmbed)->toEmbeddings();
                $validated['embedding'] = '[' . implode(',', $embeddingArray) . ']';
            } catch (\Throwable $e) {
                Log::error('Failed to update embedding for Preloved: ' . $e->getMessage());
            }
        }

        if ($request->has('images')) {
            $images = $request->input('images');
            $primaryUrl = $request->input('primary_image_url', $images[0] ?? null);
            unset($validated['images'], $validated['primary_image_url']);

            $listing->images()->delete(); 
            foreach ($images as $url) {
                $listing->images()->create([
                    'image_url' => $url,
                    'is_primary' => $url === $primaryUrl,
                ]);
            }
        }

        if ($request->has('primary_image_url') && !$request->has('images')) {
            $newPrimaryUrl = $request->input('primary_image_url');
            
            if ($listing->images()->where('image_url', $newPrimaryUrl)->exists()) {
                $listing->images()->update(['is_primary' => false]);
                $listing->images()->where('image_url', $newPrimaryUrl)->update(['is_primary' => true]);
            }

            unset($validated['primary_image_url']);
        }

        $listing->update($validated);
        
        $listing->load([
            'user:id,name,wa_number,avatar_url,status',
            'category:id,name',
            'images'
        ]);

        return $this->successResponse($listing, 'Preloved listing updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $listing = PrelovedListing::find($id);
        
        if (!$listing) {
            return $this->errorResponse('Preloved listing not found', 404);
        }

        if ($listing->user_id !== $request->user()->id) {
            return $this->errorResponse('Not authorized to modify this item', 403);
        }

        $listing->images()->delete();
        $listing->delete();

        return $this->successResponse(null, 'Preloved listing deleted successfully');
    }
}
