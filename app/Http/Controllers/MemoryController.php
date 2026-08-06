<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemoryController extends Controller
{
    // GET /api/weddings/{slug}/memories
    public function index(string $slug): JsonResponse
    {
        $wedding = Wedding::where('slug', $slug)->firstOrFail();

        return response()->json(
            $wedding->memories()->where('is_approved', true)->latest()->get()
        );
    }

    // POST /api/weddings/{slug}/memories
    public function store(Request $request, string $slug): JsonResponse
    {
        $wedding = Wedding::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
            'media' => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,mp4,mov,mp3,m4a'],
        ]);

        $mediaPath = null;
        $mediaType = null;

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $mediaPath = $file->store("memories/{$slug}", 's3');
            $mediaType = str_starts_with($file->getMimeType(), 'image') ? 'photo'
                : (str_starts_with($file->getMimeType(), 'video') ? 'video' : 'audio');
        }

        $memory = $wedding->memories()->create([
            ...$validated,
            'media_path' => $mediaPath,
            'media_type' => $mediaType,
        ]);

        return response()->json($memory, 201);
    }
}
