<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JastipListing;
use App\Models\PrelovedListing;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'type' => 'required|in:jastip,preloved' 
        ]);

        $keyword = $request->input('q');
        $type = $request->input('type');

        try {
            $embeddingArray = Str::of($keyword)->toEmbeddings();
            $vectorString = '[' . implode(',', $embeddingArray) . ']';

            if ($type === 'jastip') {
                $results = JastipListing::with([
                        'user:id,name,wa_number',
                        'category:id,name,icon'
                    ])
                    ->orderByRaw('embedding <=> ?::vector', [$vectorString])
                    ->cursorPaginate(10); 
            } else {
                $results = PrelovedListing::with([
                        'user:id,name,wa_number',
                        'category:id,name,icon'
                    ])
                    ->orderByRaw('embedding <=> ?::vector', [$vectorString])
                    ->cursorPaginate(10);
            }

            return $this->successResponse($results, 'Semantic search completed successfully');

        } catch (\Exception $e) {
            Log::error('Vector Search Error: ' . $e->getMessage());

            $errorMessage = config('app.debug') 
                ? $e->getMessage() 
                : 'Failed to process smart search. Please try again later.';
                
            return $this->errorResponse($errorMessage, 500);
        }
    }
}