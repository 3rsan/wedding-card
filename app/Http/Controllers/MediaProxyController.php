<?php

namespace App\Http\Controllers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaProxyController extends Controller
{
    // GET /api/media/{path}
    public function show(Request $request, string $path): StreamedResponse|Response
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        if (! $disk->exists($path)) {
            abort(404);
        }

        $headers = [
            'Content-Type' => $disk->mimeType($path) ?? 'application/octet-stream',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ];

        if ($request->boolean('download')) {
            $filename = basename($path);
            $headers['Content-Disposition'] = "attachment; filename=\"{$filename}\"";
        }

        return response()->stream(function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $headers);
    }
}