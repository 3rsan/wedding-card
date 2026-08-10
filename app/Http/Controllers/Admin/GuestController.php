<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Wedding;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    use AuthorizesWedding;

    public function index(Request $request, Wedding $wedding)
    {
        $this->authorizeWedding($request, $wedding);

        return $wedding->guests()
            ->with('rsvps')
            ->orderBy('display_name')
            ->get();
    }

    public function store(Request $request, Wedding $wedding)
    {
        $this->authorizeWedding($request, $wedding);

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'max_guests' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $guest = $wedding->guests()->create($data);

        return response()->json($guest, 201);
    }

    public function update(Request $request, Wedding $wedding, Guest $guest)
    {
        $this->authorizeWedding($request, $wedding);
        abort_if($guest->wedding_id !== $wedding->id, 404);

        $data = $request->validate([
            'display_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'max_guests' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        $guest->update($data);

        return response()->json($guest);
    }

    public function destroy(Request $request, Wedding $wedding, Guest $guest)
    {
        $this->authorizeWedding($request, $wedding);
        abort_if($guest->wedding_id !== $wedding->id, 404);

        $guest->delete();

        return response()->noContent();
    }
}