<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            $file = $request->file('image');

            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('uploads', $fileName);

            if (!$path) {
                Log::error('Upload Failed: path returned false for file ' . $fileName);
                return $this->errorResponse('Failed to store image to cloud storage.', 500);
            }

            $url = Storage::url($path);

            return $this->successResponse(['image_url' => $url], 'Image uploaded successfully', 201);

        } catch (\Exception $e) {
            Log::error('Upload Controller Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to process image upload. Please try again later.', 500);
        }
    }
}