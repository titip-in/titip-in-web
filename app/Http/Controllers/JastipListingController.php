<?php

namespace App\Http\Controllers;

use App\Models\JastipListing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JastipListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = JastipListing::with([
            'user:id,name,wa_number',
            'category:id,name,icon'    
        ])->latest()->get();
        return $this->successResponse($items, 'Jastip listing catalog retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'from_loc' => 'required|string|max:255',
            'to_loc' => 'required|string|max:255',
            'deadline' => 'required|date',
            'status' => 'nullable|in:ACTIVE,CLOSED',
            'image_url' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric'
        ]);

        $validated['user_id'] = $request->user()->id;

        $listing = JastipListing::create($validated);

        $listing->load([
            'user:id,name,wa_number',
            'category:id,name'    
        ]);

        return $this->successResponse($listing, 'Jastip listing posted successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $listing = JastipListing::with([
            'user:id,name,wa_number',
            'category:id,name,icon'
        ])->find($id);

        if (!$listing) {
            return $this->errorResponse('Jastip listing not found', 404);
        }

        return $this->successResponse($listing, 'Jastip listing detail retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $listing = JastipListing::find($id);
        
        if (!$listing) {
            return $this->errorResponse('Jastip listing not found', 404);
        }

        if ($listing->user_id !== $request->user()->id) {
            return $this->errorResponse('Not authorized to modify this item', 403);
        }

        $validated = $request->validate([
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'from_loc' => 'sometimes|required|string|max:255',
            'to_loc' => 'sometimes|required|string|max:255',
            'deadline' => 'sometimes|required|date',
            'status' => 'sometimes|nullable|in:ACTIVE,CLOSED',
            'image_url' => 'sometimes|nullable|string',
            'lat' => 'sometimes|nullable|numeric',
            'lng' => 'sometimes|nullable|numeric'
        ]);

        $listing->update($validated);

        $listing->load([
            'user:id,name,wa_number',
            'category:id,name'
        ]);

        return $this->successResponse($listing, 'Jastip listing updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if (!Str::isUuid($id)) {
            return $this->errorResponse('ID format not valid', 400);
        }

        $listing = JastipListing::find($id);
        
        if (!$listing) {
            return $this->errorResponse('Jastip listing not found', 404);
        }

        if ($listing->user_id !== $request->user()->id) {
            return $this->errorResponse('Not authorized to modify this item', 403);
        }

        $listing->delete();

        return $this->successResponse(null, 'Jastip listing deleted successfully');
    }
}
