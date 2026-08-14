<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Wedding;
use Illuminate\Http\JsonResponse;

class WeddingController extends Controller
{
    // GET /api/weddings/{slug}
    public function show(string $slug): JsonResponse
    {
        $wedding = Wedding::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return response()->json([
            'slug' => $wedding->slug,
            'groom_name' => $wedding->groom_name,
            'bride_name' => $wedding->bride_name,
            'wedding_date' => $wedding->wedding_date->format('Y-m-d'),
            'theme' => $wedding->theme,
            'theme_colors' => $wedding->theme_colors,
            'venues' => $wedding->venues,
            'cover_image' => $wedding->cover_image,
            'cover_image_url' => $wedding->cover_image_url,
            'hero_video' => $wedding->hero_video,
        ]);
    }

    // GET /api/weddings/{slug}/guest/{token}  -> kişiselleştirilmiş davet (isim, kaç kişi getirebilir)
    public function guestInvite(string $slug, string $token): JsonResponse
    {
        $wedding = Wedding::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $guest = $wedding->guests()->where('invite_token', $token)->firstOrFail();

        return response()->json([
            'display_name' => $guest->display_name,
            'max_guests' => $guest->max_guests,
            'existing_rsvp' => $guest->latestRsvp(),
        ]);
    }

    public function destroy(Request $request, Wedding $wedding)
    {
        $wedding->delete(); // guests, rsvps, memories cascade ile silinir mi kontrol etmemiz lazım

        return response()->noContent();
    }
}
