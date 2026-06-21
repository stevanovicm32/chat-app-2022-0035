<?php

namespace App\Presentation\Controllers;

use App\DataAccess\Models\Korisnik;
use App\DataAccess\Models\Uloga;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class StatsController extends BaseController
{
    public function index(): JsonResponse
    {
        $korisniciPoUlozi = Uloga::query()
            ->withCount('korisnici')
            ->get()
            ->map(fn ($uloga) => [
                'uloga' => $uloga->naziv,
                'broj' => $uloga->korisnici_count,
            ]);

        $chatStats = ['ukupnoChatova' => 0, 'ukupnoPoruka' => 0];
        $chatUrl = config('app.chat_service_url', env('CHAT_SERVICE_URL'));

        if ($chatUrl) {
            try {
                $response = Http::timeout(3)->get(rtrim($chatUrl, '/').'/api/stats');
                if ($response->successful()) {
                    $chatStats = $response->json('data', $chatStats);
                }
            } catch (\Exception $e) {
                // Chat servis nedostupan
            }
        }

        return response()->json([
            'success' => true,
            'data' => array_merge([
                'korisniciPoUlozi' => $korisniciPoUlozi,
                'ukupnoKorisnika' => Korisnik::count(),
            ], $chatStats),
        ]);
    }
}
