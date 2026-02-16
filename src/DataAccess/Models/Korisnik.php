<?php

namespace App\DataAccess\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Korisnik extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'korisnik';
    protected $primaryKey = 'idKorisnik';
    public $incrementing = true;

    protected $fillable = [
        'email',
        'lozinka',
        'suspendovan',
        'idUloga',
    ];

    protected $hidden = [
        'lozinka',
    ];

    protected $casts = [
        'suspendovan' => 'date',
    ];

    public function uloga(): BelongsTo
    {
        return $this->belongsTo(Uloga::class, 'idUloga', 'idUloga');
    }

    public function chatovi(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'pripada', 'idKorisnik', 'idChat')
                    ->withPivot('datumKreiranja')
                    ->withTimestamps();
    }

    public function poruke(): HasMany
    {
        return $this->hasMany(Poruka::class, 'idKorisnik', 'idKorisnik');
    }
}

