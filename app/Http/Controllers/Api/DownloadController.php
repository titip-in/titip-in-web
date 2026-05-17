<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    public function downloadAndroid(): BinaryFileResponse|\Illuminate\Http\JsonResponse
    {
        $filePath = '/var/www/titip-downloads/android/latest.apk';

        if (!file_exists($filePath)) {
            return response()->json([
                'status' => 404,
                'error' => 'Not Found',
                'message' => 'Berkas aplikasi versi terbaru belum tersedia di server.'
            ], 404);
        }

        $downloadName = 'titipin-latest.apk';

        $headers = [
            'Content-Type' => 'application/vnd.android.package-archive',
        ];

        return response()->download($filePath, $downloadName, $headers);
    }
}