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
     * in the listing's photos array.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // 5MB
        ]);

        $file = $data['photo'];
        $path = $file->store('listings', 'public');

        return response()->json([
            'url' => asset('storage/'.$path),
            'path' => $path,
        ], 201);
    }
}
