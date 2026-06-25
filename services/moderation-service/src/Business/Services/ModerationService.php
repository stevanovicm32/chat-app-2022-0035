<?php

namespace App\Business\Services;

use App\DataAccess\Models\Prijava;
use App\DataAccess\Models\Sankcija;
use App\Infrastructure\Messaging\KafkaProducer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ModerationService
{
    private const ADMIN_ROLE_ID = 1;
    private const MODERATOR_ROLE_ID = 3;
    private const TOPIC_SUSPEND_REQUEST = 'suspenzija-zahtev';

    public function __construct(
        private KafkaProducer $kafkaProducer
    ) {}

    public function getAllPrijave(array $filters = []): Collection
    {
        $query = Prijava::with('sankcije')->orderByDesc('datum');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    public function getPrijavaById(int $id): ?Prijava
    {
        return Prijava::with('sankcije')->find($id);
    }

    public function createPrijava(array $data): Prijava
    {
        return Prijava::create([
            'podnosilac_id_ref' => $data['podnosilac_id_ref'],
            'poruka_id_ref' => $data['poruka_id_ref'] ?? null,
            'optuzeni_id_ref' => $data['optuzeni_id_ref'] ?? null,
            'status' => $data['status'] ?? 'na_cekanju',
            'datum' => $data['datum'] ?? Carbon::today(),
        ]);
    }

    public function updatePrijavaStatus(int $id, string $status): Prijava
    {
        $prijava = Prijava::find($id);
        if (!$prijava) {
            throw new \Exception('Prijava nije pronađena', 404);
        }

        if (!in_array($status, ['na_cekanju', 'odobreno', 'odbijeno'], true)) {
            throw new \Exception('Neispravan status', 422);
        }

        $prijava->status = $status;
        $prijava->save();

        return $prijava->fresh('sankcije');
    }

    public function createSankcija(int $prijavaId, int $moderatorId, int $days = 3): array
    {
        $prijava = Prijava::find($prijavaId);
        if (!$prijava) {
            throw new \Exception('Prijava nije pronađena', 404);
        }

        $sankcija = Sankcija::create([
            'datum_isteka' => Carbon::now()->addDays($days),
            'moderator_id_ref' => $moderatorId,
            'idPrijava' => $prijavaId,
        ]);

        $prijava->status = 'odobreno';
        $prijava->save();

        $optuzeniId = $prijava->optuzeni_id_ref;
        $korisnik = null;
        if ($optuzeniId) {
            $korisnik = $this->dispatchSuspendEvent($optuzeniId, $days, $sankcija->idSankcija, $prijavaId, $moderatorId);
        }

        return [
            'sankcija' => $sankcija->load('prijava'),
            'korisnik' => $korisnik,
        ];
    }

    public function suspendKorisnikDirect(int $targetId, object $moderator, int $days = 3): array
    {
        if (!$this->canModerate($moderator)) {
            throw new \Exception('Nemate dozvolu za suspendeziranje korisnika', 403);
        }

        $prijava = $this->createPrijava([
            'podnosilac_id_ref' => $moderator->idKorisnik,
            'poruka_id_ref' => null,
            'optuzeni_id_ref' => $targetId,
            'status' => 'odobreno',
            'datum' => Carbon::today(),
        ]);

        $sankcija = Sankcija::create([
            'datum_isteka' => Carbon::now()->addDays($days),
            'moderator_id_ref' => $moderator->idKorisnik,
            'idPrijava' => $prijava->idPrijava,
        ]);

        $korisnik = $this->dispatchSuspendEvent(
            $targetId,
            $days,
            $sankcija->idSankcija,
            $prijava->idPrijava,
            $moderator->idKorisnik
        );

        return [
            'sankcija' => $sankcija->load('prijava'),
            'korisnik' => $korisnik,
            'async' => true,
        ];
    }

    /**
     * Saga korak 1: isključivo Kafka — publikuje suspenzija-zahtev.
     */
    private function dispatchSuspendEvent(
        int $korisnikId,
        int $days,
        int $sankcijaId,
        int $prijavaId,
        int $moderatorId
    ): ?array {
        $correlationId = (string) Str::uuid();

        $this->kafkaProducer->publish(self::TOPIC_SUSPEND_REQUEST, [
            'correlationId' => $correlationId,
            'korisnikId' => $korisnikId,
            'days' => $days,
            'sankcijaId' => $sankcijaId,
            'prijavaId' => $prijavaId,
            'moderatorId' => $moderatorId,
        ], (string) $korisnikId);

        return null;
    }

    public function getAllSankcije(): Collection
    {
        return Sankcija::with('prijava')->orderByDesc('created_at')->get();
    }

    private function canModerate(object $user): bool
    {
        return in_array($user->idUloga ?? null, [self::ADMIN_ROLE_ID, self::MODERATOR_ROLE_ID], true);
    }
}
