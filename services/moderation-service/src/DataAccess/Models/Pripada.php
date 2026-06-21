<?php

namespace App\DataAccess\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pripada extends Model
{
    use HasFactory;

    protected $table = 'pripada';

    protected $fillable = [
        'idKorisnik',
        'idChat',
        'datumKreiranja',
    ];

    protected $casts = [
        'datumKreiranja' => 'date',
    ];

    public function korisnik(): BelongsTo
    {
        return $this->belongsTo(Korisnik::class, 'idKorisnik', 'idKorisnik');
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'idChat', 'idChat');
    }
}

