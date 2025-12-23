<?php

namespace App\DataAccess\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Uloga extends Model
{
    use HasFactory;

    protected $table = 'uloga';
    protected $primaryKey = 'idUloga';

    protected $fillable = [
        'naziv',
    ];

    public function korisnici(): HasMany
    {
        return $this->hasMany(Korisnik::class, 'idUloga', 'idUloga');
    }
}

