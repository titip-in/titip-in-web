<?php

namespace App\Http\Controllers;

use App\Models\PrelovedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrelovedItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = PrelovedItem::with([
            'user:id,name',
            'category:id,name,icon'
        ])->latest()->get();

        return $this->successResponse($items, 'Preloved catalog retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:WTS,WTB',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'condition' => 'required|in:NEW,GOOD,FAIR',
            'images' => 'nullable|array',
            'images.*' => 'string',
        ]);

        $validated['user_id'] = $request->user()->id;

        $preloved = PrelovedItem::create($validated);

        $preloved->load([
            'user:id,name',
            'category:id,name'
        ]);

        return $this->successResponse($preloved, 'Preloved item posted successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = PrelovedItem::find($id);
       
        if (!$item) {
            return $this->errorResponse('Item not found', 404);
        }

        return $this->successResponse($item, 'Preloved item details retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = PrelovedItem::find($id);

        if (!$item) {
            return $this->errorResponse('Item not found', 404);
        }

        if ($item->user_id !== $request->user()->id) {
            return $this->errorResponse('Not authorized to modify this item.', 403);
        }

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'type'        => 'sometimes|in:WTS,WTB',
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|integer|min:0',
            'condition'   => 'sometimes|in:NEW,GOOD,FAIR',
            'images'      => 'nullable|array',
            'images.*'    => 'string',
            'is_sold'     => 'sometimes|boolean',
        ]);

        $item->update($validated);
        $item->load(['user:id,name', 'category:id,name']);

        return $this->successResponse($item, 'Preloved item updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = PrelovedItem::find($id);
       
        if (!$item) {
            return $this->errorResponse('Item not found', 404);
        }

        $item->delete();

        return $this->successResponse(null, 'Preloved item deleted successfully');
    }
}
