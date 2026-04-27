<?php

namespace App\Http\Controllers;

use App\Models\PrelovedRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PrelovedRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = PrelovedRequest::with([
            'user:id,name',
            'category:id,name,icon'
        ])->latest()->get();

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

        $reqItem = PrelovedRequest::create($validated);

        $reqItem->load([
            'user:id,name',
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
            'user:id,name',
            'category:id,name,icon'
        ])->find($id);

        if (!$reqItem) {
            return $this->errorResponse('Preloved request not found', 404);
        }

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

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'max_price' => 'nullable|integer|min:0',
            'status' => 'sometimes|in:OPEN,FOUND,CLOSED'
        ]);

        $reqItem->update($validated);

        $reqItem->load([
            'user:id,name',
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
