<?php

namespace App\DataAccess\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poruka extends Model
{
    use HasFactory;

    protected $table = 'poruka';
    protected $primaryKey = 'idPoruka';

    protected $fillable = [
        'tekst',
        'idChat',
        'idKorisnik',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'idChat', 'idChat');
    }

    public function korisnik(): BelongsTo
    {
        return $this->belongsTo(Korisnik::class, 'idKorisnik', 'idKorisnik');
    }

    public function datoteke(): HasMany
    {
        return $this->hasMany(Datoteka::class, 'idPoruka', 'idPoruka');
    }
}

