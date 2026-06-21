<?php

namespace App\DataAccess\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sankcija extends Model
{
    protected $table = 'sankcija';
    protected $primaryKey = 'idSankcija';

    protected $fillable = [
        'datum_isteka',
        'moderator_id_ref',
        'idPrijava',
    ];

    protected $casts = [
        'datum_isteka' => 'date',
    ];

    public function prijava(): BelongsTo
    {
        return $this->belongsTo(Prijava::class, 'idPrijava', 'idPrijava');
    }
}
