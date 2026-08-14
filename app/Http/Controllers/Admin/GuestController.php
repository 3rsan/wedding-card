<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function import(Request $request, Wedding $wedding)
    {
        $this->authorizeWedding($request, $wedding);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        // İlk satırı başlık olarak oku, Excel'in ; ayracını da destekle
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = str_contains($firstLine, ';') ? ';' : ',';

        $header = fgetcsv($handle, 0, $delimiter);
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        $nameIndex = array_search('display_name', $header) !== false
            ? array_search('display_name', $header)
            : array_search('isim', $header);
        $phoneIndex = array_search('phone', $header) !== false
            ? array_search('phone', $header)
            : array_search('telefon', $header);
        $maxGuestsIndex = array_search('max_guests', $header) !== false
            ? array_search('max_guests', $header)
            : array_search('max kişi', $header);

        if ($nameIndex === false) {
            fclose($handle);
            return response()->json([
                'message' => 'CSV dosyasında "display_name" veya "isim" sütunu bulunamadı.',
            ], 422);
        }

        $created = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1;

        DB::transaction(function () use ($handle, $delimiter, $nameIndex, $phoneIndex, $maxGuestsIndex, $wedding, &$created, &$skipped, &$errors, &$rowNumber) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNumber++;

                $displayName = trim($row[$nameIndex] ?? '');

                if ($displayName === '') {
                    $skipped++;
                    $errors[] = "Satır {$rowNumber}: isim boş, atlandı.";
                    continue;
                }

                $wedding->guests()->create([
                    'display_name' => $displayName,
                    'phone' => $phoneIndex !== false ? trim($row[$phoneIndex] ?? '') ?: null : null,
                    'max_guests' => $maxGuestsIndex !== false && is_numeric($row[$maxGuestsIndex] ?? null)
                        ? (int) $row[$maxGuestsIndex]
                        : 1,
                ]);

                $created++;
            }
        });

        fclose($handle);

        return response()->json([
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ]);
    }
}