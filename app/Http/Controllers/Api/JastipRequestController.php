<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JastipRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class JastipRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = JastipRequest::with([
            'user:id,name,wa_number,avatar_url,status',
            'category:id,name,icon'
        ])
        ->where('status', 'OPEN') 
        ->latest()->get();

        return $this->successResponse($items, 'Jastip request catalog retrieved successfully');
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
            'from_loc' => 'required|string|max:255',
            'to_loc' => 'required|string|max:255',
            'status' => 'nullable|in:OPEN,TAKEN,CLOSED'
        ]);

        $validated['user_id'] = $request->user()->id;

        try {
            $categoryName = Category::find($validated['category_id'])?->name ?? '';
            $textToEmbed = trim($categoryName . ' - ' . $validated['title'] . ' ' . ($validated['description'] ?? '') . ' - Request Jastip dari ' . $validated['from_loc'] . ' ke ' . $validated['to_loc']);
            
            $embeddingArray = Str::of($textToEmbed)->toEmbeddings();
            $validated['embedding'] = '[' . implode(',', $embeddingArray) . ']';
        } catch (\Throwable $e) {
            Log::error('Failed to generate embedding for Jastip Request: ' . $e->getMessage());
        }

        $reqItem = JastipRequest::create($validated);

        $reqItem->load([
            'user:id,name,wa_number,avatar_url,status',
            'category:id,name'
        ]);

        return $this->successResponse($reqItem, 'Jastip request posted successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $reqItem = JastipRequest::with([
            'user:id,name,wa_number,avatar_url,status',
            'category:id,name,icon'
        ])->find($id);

        if (!$reqItem) {
            return $this->errorResponse('Jastip request not found', 404);
        }

        if ($reqItem->status !== 'OPEN' && $reqItem->user_id !== auth('sanctum')->id()) {
            return $this->errorResponse('This Jastip request is not open and cannot be viewed by the public.', 403);
        }

        return $this->successResponse($reqItem, 'Jastip request detail retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $reqItem = JastipRequest::find($id);
        
        if (!$reqItem) {
            return $this->errorResponse('Jastip request not found', 404);
        }

        if ($reqItem->user_id !== $request->user()->id) {
            return $this->errorResponse('Not authorized to modify this item', 403);
        }

        $isReactivating = $reqItem->status !== 'OPEN' && $request->input('status') === 'OPEN';
        if ($isReactivating) {
            if (!$request->user()->canAddItem()) {
                $maxLimit = $request->user()->getMaxItemLimit();
                $tierName = strtoupper($request->user()->tier->value);
                return $this->errorResponse("Failed to reactivate. Your {$tierName} tier has reached the maximum limit of {$maxLimit} active items.", 400);
            }
            $reqItem->created_at = now();
        }

        $validated = $request->validate([
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'from_loc' => 'sometimes|required|string|max:255',
            'to_loc' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|nullable|in:OPEN,TAKEN,CLOSED'
        ]);

        if (isset($validated['title']) || isset($validated['description']) || isset($validated['category_id']) || isset($validated['from_loc']) || isset($validated['to_loc'])) {
            try {
                $newTitle = $validated['title'] ?? $reqItem->title;
                $newDesc = $validated['description'] ?? $reqItem->description;
                $newFromLoc = $validated['from_loc'] ?? $reqItem->from_loc;
                $newToLoc = $validated['to_loc'] ?? $reqItem->to_loc;
                $newCatId = $validated['category_id'] ?? $reqItem->category_id;
                
                $categoryName = Category::find($newCatId)?->name ?? '';
                $textToEmbed = trim($categoryName . ' - ' . $newTitle . ' ' . $newDesc . ' - Request Jastip dari ' . $newFromLoc . ' ke ' . $newToLoc);
                
                $embeddingArray = Str::of($textToEmbed)->toEmbeddings();
                $validated['embedding'] = '[' . implode(',', $embeddingArray) . ']';
            } catch (\Throwable $e) {
                Log::error('Failed to update embedding for Jastip Request: ' . $e->getMessage());
            }
        }

        $reqItem->update($validated);
        
        $reqItem->load([
            'user:id,name,wa_number,avatar_url,status',
            'category:id,name'
        ]);

        return $this->successResponse($reqItem, 'Jastip request updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $reqItem = JastipRequest::find($id);
        
        if (!$reqItem) {
            return $this->errorResponse('Jastip request not found', 404);
        }

        if ($reqItem->user_id !== $request->user()->id) {
            return $this->errorResponse('Not authorized to modify this item', 403);
        }

        $reqItem->delete();

        return $this->successResponse(null, 'Jastip request deleted successfully');
    }
}
