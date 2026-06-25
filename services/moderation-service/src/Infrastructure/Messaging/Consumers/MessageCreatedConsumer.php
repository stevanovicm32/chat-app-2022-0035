<?php

namespace App\Infrastructure\Messaging\Consumers;

use App\DataAccess\Models\Prijava;
use App\DataAccess\Models\Sankcija;
use Illuminate\Support\Facades\Log;

class MessageCreatedConsumer
{
    public const TOPIC_IN = 'poruka-kreirana';

    private const FLAGGED_WORDS = ['spam', 'uvreda', 'mržnja', 'hack'];

    public function handle(array $payload): void
    {
        $text = strtolower($payload['tekst'] ?? '');
        $senderId = (int) ($payload['senderId'] ?? 0);
        $porukaId = (int) ($payload['porukaId'] ?? 0);

        foreach (self::FLAGGED_WORDS as $word) {
            if (str_contains($text, $word)) {
                Prijava::create([
                    'podnosilac_id_ref' => 1,
                    'poruka_id_ref' => $porukaId,
                    'optuzeni_id_ref' => $senderId,
                    'status' => 'na_cekanju',
                    'datum' => now()->toDateString(),
                ]);
                Log::info('Auto-prijava kreirana iz Kafka događaja', ['porukaId' => $porukaId]);
                break;
            }
        }
    }
}
