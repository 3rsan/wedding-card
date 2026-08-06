<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Rsvp;
use App\Models\Wedding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RsvpController extends Controller
{
    // POST /api/weddings/{slug}/guest/{token}/rsvp
    public function store(Request $request, string $slug, string $token): JsonResponse
    {
        $wedding = Wedding::where('slug', $slug)->firstOrFail();
        $guest = $wedding->guests()->where('invite_token', $token)->firstOrFail();

        $validated = $request->validate([
            'attending' => ['required', 'boolean'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:' . $guest->max_guests],
            'attendee_names' => ['nullable', 'array'],
            'attendee_names.*' => ['string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $rsvp = $guest->rsvps()->create($validated);

        return response()->json($rsvp, 201);
    }

    // GET /api/weddings/{slug}/rsvps  -> herkese açık katılım listesi (attığın örnekteki gibi)
    public function index(string $slug): JsonResponse
    {
        $wedding = Wedding::where('slug', $slug)->firstOrFail();

        $guests = $wedding->guests()
            ->with(['rsvps' => fn ($q) => $q->latest()->limit(1)])
            ->get()
            ->filter(fn ($guest) => $guest->rsvps->isNotEmpty())
            ->map(function ($guest) {
                $rsvp = $guest->rsvps->first();
                return [
                    'display_name' => $guest->display_name,
                    'attending' => $rsvp->attending,
                    'guest_count' => $rsvp->guest_count,
                    'attendee_names' => $rsvp->attendee_names,
                ];
            })
            ->values();

        return response()->json($guests);
    }
}
