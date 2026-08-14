<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Memory;
use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

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

    public function downloadAll(Request $request, Wedding $wedding)
    {
        $this->authorizeWedding($request, $wedding);

        $memories = $wedding->memories()->whereNotNull('media_path')->get();

        if ($memories->isEmpty()) {
            return response()->json(['message' => 'İndirilecek medya yok.'], 404);
        }

        $zipFileName = "{$wedding->slug}-anilar-" . now()->format('Y-m-d') . '.zip';
        $tempPath = storage_path("app/tmp/{$zipFileName}");

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $zip = new ZipArchive();
        $zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($memories as $memory) {
            $contents = Storage::disk('s3')->get($memory->media_path);
            $extension = pathinfo($memory->media_path, PATHINFO_EXTENSION);
            $fileName = ($memory->first_name ?? 'misafir') . "-{$memory->id}.{$extension}";
            $zip->addFromString($fileName, $contents);
        }

        $zip->close();

        return response()->download($tempPath, $zipFileName)->deleteFileAfterSend(true);
    }
}