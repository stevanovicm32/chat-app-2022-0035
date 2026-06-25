<?php

namespace App\Infrastructure\Messaging\Consumers;

use App\DataAccess\Models\Prijava;
use App\DataAccess\Models\Sankcija;
use Illuminate\Support\Facades\Log;

class ProcessingErrorConsumer
{
    public const TOPIC_IN = 'greska-pri-obradi';

    /** Saga kompenzacija — poništava sankciju ako Identity ne uspe */
    public function handle(array $payload): void
    {
        $sankcijaId = (int) ($payload['payload']['sankcijaId'] ?? $payload['sankcijaId'] ?? 0);
        if (!$sankcijaId) {
            return;
        }

        $sankcija = Sankcija::find($sankcijaId);
        if (!$sankcija) {
            return;
        }

        $prijava = Prijava::find($sankcija->idPrijava);
        if ($prijava) {
            $prijava->status = 'odbijeno';
            $prijava->save();
        }
        $sankcija->delete();

        Log::warning('Saga kompenzacija: sankcija otkazana', [
            'sankcijaId' => $sankcijaId,
            'error' => $payload['error'] ?? 'unknown',
        ]);
    }
}
