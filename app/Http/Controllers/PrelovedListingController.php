<?php

namespace App\Http\Controllers;

use App\Models\PrelovedListing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PrelovedListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = PrelovedListing::with([
            'user:id,name,wa_number',
            'category:id,name,icon'
        ])->latest()->get();
        
        return $this->successResponse($items, 'Preloved listing catalog retrieved successfully');
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
            'price' => 'required|integer|min:0',
            'condition' => 'required|in:NEW,LIKE_NEW,GOOD,FAIR',
            'image_url' => 'nullable|string',
            'status' => 'nullable|in:AVAILABLE,SOLD,RESERVED'
        ]);

        $validated['user_id'] = $request->user()->id;

        $listing = PrelovedListing::create($validated);

        $listing->load([
            'user:id,name,wa_number', 
            'category:id,name'
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
            'user:id,name,wa_number',
            'category:id,name,icon'
        ])->find($id);

        if (!$listing) {
            return $this->errorResponse('Preloved listing not found', 404);
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

        $validated = $request->validate([
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|integer|min:0',
            'condition' => 'sometimes|required|in:NEW,LIKE_NEW,GOOD,FAIR',
            'image_url' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|in:AVAILABLE,SOLD,RESERVED'
        ]);

        $listing->update($validated);
        
        $listing->load([
            'user:id,name,wa_number',
            'category:id,name'
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

        $listing->delete();

        return $this->successResponse(null, 'Preloved listing deleted successfully');
    }
}
