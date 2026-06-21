<?php

namespace App\DataAccess\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chat';
    protected $primaryKey = 'idChat';

    protected $fillable = [
        'idChat',
    ];

    public function korisnici(): BelongsToMany
    {
        return $this->belongsToMany(Korisnik::class, 'pripada', 'idChat', 'idKorisnik')
                    ->withPivot('datumKreiranja')
                    ->withTimestamps();
    }

    public function poruke(): HasMany
    {
        return $this->hasMany(Poruka::class, 'idChat', 'idChat');
    }

    public function grupniChat(): HasOne
    {
        return $this->hasOne(GrupniChat::class, 'idChat', 'idChat');
    }

    public function privatniChat(): HasOne
    {
        return $this->hasOne(PrivatniChat::class, 'idChat', 'idChat');
    }
}

