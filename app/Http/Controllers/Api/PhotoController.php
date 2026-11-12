<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoController extends Controller
{
    /**
     * Upload a listing photo. Returns a public URL the client stores
     * in the listing's photos array. Uses the default filesystem disk —
     * S3/R2 object storage in production, local disk in development.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // 5MB
        ]);

        $file = $data['photo'];
        $disk = (string) config('filesystems.default', 'local');
        $path = $file->store('listings', $disk);

        return response()->json([
            'url' => Storage::disk($disk)->url($path),
            'path' => $path,
        ], 201);
    }
}
