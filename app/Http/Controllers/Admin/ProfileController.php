<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Mevcut şifre hatalı.'],
            ]);
        }

        $user->update(['password' => bcrypt($data['new_password'])]);
        $user->tokens()->delete(); // tüm token'ları geçersiz kıl, yeniden login gerekli

        return response()->json(['message' => 'Şifre güncellendi.']);
    }
}