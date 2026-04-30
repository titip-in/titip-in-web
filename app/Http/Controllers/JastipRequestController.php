<?php

namespace App\Http\Controllers;

use App\Models\JastipRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JastipRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = JastipRequest::with([
            'user:id,name,wa_number',
            'category:id,name,icon'
        ])->latest()->get();

        return $this->successResponse($items, 'Jastip request catalog retrieved successfully');
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
            'notes' => 'nullable|string',
            'status' => 'nullable|in:OPEN,TAKEN,CLOSED'
        ]);

        $validated['user_id'] = $request->user()->id;

        $reqItem = JastipRequest::create($validated);

        $reqItem->load([
            'user:id,name,wa_number',
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
            'user:id,name,wa_number',
            'category:id,name,icon'
        ])->find($id);

        if (!$reqItem) {
            return $this->errorResponse('Jastip request not found', 404);
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

        $validated = $request->validate([
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'from_loc' => 'sometimes|required|string|max:255',
            'to_loc' => 'sometimes|required|string|max:255',
            'notes' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|in:OPEN,TAKEN,CLOSED'
        ]);

        $reqItem->update($validated);
        
        $reqItem->load([
            'user:id,name,wa_number',
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
