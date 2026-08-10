<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Memory;
use App\Models\Wedding;
use Illuminate\Http\Request;

class MemoryController extends Controller
{
    use AuthorizesWedding;

    public function index(Request $request, Wedding $wedding)
    {
        $this->authorizeWedding($request, $wedding);

        $status = $request->query('status'); // 'pending' | 'approved' | null (hepsi)

        $query = $wedding->memories()->latest();

        if ($status === 'pending') {
            $query->where('is_approved', false);
        } elseif ($status === 'approved') {
            $query->where('is_approved', true);
        }

        return $query->get();
    }

    public function approve(Request $request, Wedding $wedding, Memory $memory)
    {
        $this->authorizeWedding($request, $wedding);
        abort_if($memory->wedding_id !== $wedding->id, 404);

        $memory->update(['is_approved' => true]);

        return response()->json($memory);
    }

    public function reject(Request $request, Wedding $wedding, Memory $memory)
    {
        $this->authorizeWedding($request, $wedding);
        abort_if($memory->wedding_id !== $wedding->id, 404);

        $memory->update(['is_approved' => false]);

        return response()->json($memory);
    }

    public function destroy(Request $request, Wedding $wedding, Memory $memory)
    {
        $this->authorizeWedding($request, $wedding);
        abort_if($memory->wedding_id !== $wedding->id, 404);

        $memory->delete();

        return response()->noContent();
    }
}