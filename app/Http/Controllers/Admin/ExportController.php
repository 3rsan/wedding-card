<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    use AuthorizesWedding;

    public function guests(Request $request, Wedding $wedding): StreamedResponse
    {
        $this->authorizeWedding($request, $wedding);

        $guests = $wedding->guests()->with('rsvps')->orderBy('display_name')->get();

        $filename = "{$wedding->slug}-misafirler.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () use ($guests) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // Excel için UTF-8 BOM

            fputcsv($handle, ['İsim', 'Telefon', 'Max Kişi', 'Durum', 'Katılımcı Sayısı', 'Katılımcı İsimleri', 'Not']);

            foreach ($guests as $guest) {
                $rsvp = $guest->latestRsvp();

                fputcsv($handle, [
                    $guest->display_name,
                    $guest->phone,
                    $guest->max_guests,
                    $rsvp ? ($rsvp->attending ? 'Katılıyor' : 'Katılmıyor') : 'Bekliyor',
                    $rsvp?->guest_count,
                    $rsvp?->attendee_names ? implode(', ', $rsvp->attendee_names) : '',
                    $rsvp?->note,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}