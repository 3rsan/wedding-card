<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use Illuminate\Http\Request;

class WeddingSettingsController extends Controller
{
    use AuthorizesWedding;

    public function show(Request $request, Wedding $wedding)
    {
        $this->authorizeWedding($request, $wedding);

        return response()->json([
            ...$wedding->only([
                'id', 'slug', 'groom_name', 'bride_name', 'wedding_date',
                'theme', 'theme_colors', 'cover_image', 'hero_video',
            ]),
            'cover_image_url' => $wedding->cover_image_url,
        ]);
    }

    public function update(Request $request, Wedding $wedding)
    {
        $this->authorizeWedding($request, $wedding);

        $data = $request->validate([
            'theme_colors' => ['sometimes', 'array'],
            'theme_colors.primary' => ['sometimes', 'string', 'max:20'],
            'theme_colors.text' => ['sometimes', 'string', 'max:20'],
            'theme_colors.bg' => ['sometimes', 'string', 'max:20'],
        ]);

        if (isset($data['theme_colors'])) {
            $wedding->theme_colors = array_merge($wedding->theme_colors ?? [], $data['theme_colors']);
        }

        $wedding->save();

        return response()->json($wedding->only(['theme_colors']));
    }

    public function uploadCover(Request $request, Wedding $wedding)
    {
        $this->authorizeWedding($request, $wedding);

        $request->validate([
            'cover_image' => ['required', 'file', 'image', 'max:10240'], // 10MB
        ]);

        $path = $request->file('cover_image')->store("weddings/{$wedding->slug}", 's3');

        $wedding->update(['cover_image' => $path]);

        return response()->json(['cover_image' => $path]);
    }
}