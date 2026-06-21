<?php

namespace App\DataAccess\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prijava extends Model
{
    protected $table = 'prijava';
    protected $primaryKey = 'idPrijava';

    protected $fillable = [
        'podnosilac_id_ref',
        'poruka_id_ref',
        'optuzeni_id_ref',
        'status',
        'datum',
    ];

    protected $casts = [
        'datum' => 'date',
    ];

    public function sankcije(): HasMany
    {
        return $this->hasMany(Sankcija::class, 'idPrijava', 'idPrijava');
    }
}
