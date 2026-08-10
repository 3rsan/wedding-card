<?php

namespace App\Http\Controllers\Admin;

use App\Models\Wedding;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait AuthorizesWedding
{
    protected function authorizeWedding(Request $request, Wedding $wedding): void
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'couple' && $wedding->owner_user_id === $user->id) {
            return;
        }

        throw new HttpException(403, 'Bu düğüne erişim yetkiniz yok.');
    }
}