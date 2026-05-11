<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

            $path = $file->storeAs('uploads', $fileName, 'public');

            $url = url('/storage/' . $path);

            return $this->successResponse(['image_url' => $url], 'Image uploaded successfully', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload image: ' . $e->getMessage(), 500);
        }
    }
}