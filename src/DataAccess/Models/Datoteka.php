<?php

namespace App\DataAccess\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Datoteka extends Model
{
    use HasFactory;

    protected $table = 'datoteka';
    protected $primaryKey = 'idDatoteka';

    protected $fillable = [
        'putanja',
        'tip',
        'idPoruka',
    ];

    public function poruka(): BelongsTo
    {
        return $this->belongsTo(Poruka::class, 'idPoruka', 'idPoruka');
    }
}
