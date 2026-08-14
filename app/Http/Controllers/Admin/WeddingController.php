<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WeddingController extends Controller
{
    public function index(Request $request)
    {
        return Wedding::withCount('guests')->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'alpha_dash', 'max:255', 'unique:weddings,slug'],
            'groom_name' => ['required', 'string', 'max:255'],
            'bride_name' => ['required', 'string', 'max:255'],
            'wedding_date' => ['required', 'date'],
            'couple_email' => ['required', 'email', 'unique:users,email'],
            'couple_password' => ['nullable', 'string', 'min:6'],
        ]);

        $password = $data['couple_password'] ?? Str::random(10);

        $result = DB::transaction(function () use ($data, $password) {
            $couple = User::create([
                'name' => "{$data['bride_name']} & {$data['groom_name']}",
                'email' => $data['couple_email'],
                'password' => bcrypt($password),
                'role' => 'couple',
            ]);

            $wedding = Wedding::create([
                'slug' => $data['slug'],
                'groom_name' => $data['groom_name'],
                'bride_name' => $data['bride_name'],
                'wedding_date' => $data['wedding_date'],
                'theme' => 'classic',
                'is_published' => true,
                'owner_user_id' => $couple->id,
                'theme_colors' => ['primary' => '#d4a04a', 'text' => '#2c3e50', 'bg' => '#f7f3eb'],
                'default_theme_colors' => ['primary' => '#d4a04a', 'text' => '#2c3e50', 'bg' => '#f7f3eb'],
            ]);

            return [$wedding, $couple];
        });

        [$wedding, $couple] = $result;

        return response()->json([
            'wedding' => $wedding,
            'couple' => [
                'email' => $couple->email,
                'password' => $password, // sadece bu ilk response'ta gösteriyoruz, sonra saklanmıyor
            ],
        ], 201);
    }

    public function destroy(Request $request, Wedding $wedding)
    {
        $wedding->delete(); // guests, rsvps, memories cascade ile silinir mi kontrol etmemiz lazım

        return response()->noContent();
    }
}