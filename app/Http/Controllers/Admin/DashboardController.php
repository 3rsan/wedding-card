<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use AuthorizesWedding;

    public function index(Request $request, Wedding $wedding)
    {
        $this->authorizeWedding($request, $wedding);

        $guests = $wedding->guests()->with('rsvps')->get();

        $totalGuests = $guests->count();
        $withRsvp = $guests->filter(fn ($g) => $g->latestRsvp() !== null);

        $attending = $withRsvp->filter(fn ($g) => $g->latestRsvp()->attending);
        $notAttending = $withRsvp->filter(fn ($g) => ! $g->latestRsvp()->attending);
        $pending = $totalGuests - $withRsvp->count();

        $totalAttendeeCount = $attending->sum(fn ($g) => $g->latestRsvp()->guest_count);

        return response()->json([
            'wedding_slug' => $wedding->slug,
            'bride_name' => $wedding->bride_name,
            'groom_name' => $wedding->groom_name,
            'total_invited' => $totalGuests,
            'attending' => $attending->count(),
            'not_attending' => $notAttending->count(),
            'pending' => $pending,
            'total_attendee_count' => $totalAttendeeCount,
        ]);
    }
}