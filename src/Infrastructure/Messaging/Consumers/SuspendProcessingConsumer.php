<?php

namespace App\Infrastructure\Messaging\Consumers;

use App\Business\Services\KorisnikService;
use App\Infrastructure\Messaging\KafkaProducer;
use Illuminate\Support\Facades\Log;

class SuspendProcessingConsumer
{
    public const TOPIC_IN = 'suspenzija-obrada';
    public const TOPIC_OUT = 'suspenzija-primena';
    public const TOPIC_ERROR = 'greska-pri-obradi';

    public function __construct(
        private KorisnikService $korisnikService,
        private KafkaProducer $kafkaProducer
    ) {}

    public function handle(array $payload): void
    {
        try {
            $korisnikId = (int) ($payload['korisnikId'] ?? 0);
            $days = (int) ($payload['days'] ?? 3);
            $correlationId = $payload['correlationId'] ?? uniqid('saga-');

            $korisnik = $this->korisnikService->suspendKorisnik($korisnikId, max(1, $days));

            $this->kafkaProducer->publish(self::TOPIC_OUT, [
                'correlationId' => $correlationId,
                'korisnikId' => $korisnikId,
                'days' => $days,
                'sankcijaId' => $payload['sankcijaId'] ?? null,
                'prijavaId' => $payload['prijavaId'] ?? null,
                'suspendovan' => $korisnik->suspendovan?->toDateString(),
                'status' => 'applied',
            ], (string) $korisnikId);
        } catch (\Throwable $e) {
            Log::error('SuspendProcessingConsumer failed', ['error' => $e->getMessage(), 'payload' => $payload]);
            $this->kafkaProducer->publish(self::TOPIC_ERROR, [
                'correlationId' => $payload['correlationId'] ?? null,
                'source' => 'identity-service',
                'error' => $e->getMessage(),
                'originalTopic' => self::TOPIC_IN,
                'payload' => $payload,
            ]);
        }
    }
}
