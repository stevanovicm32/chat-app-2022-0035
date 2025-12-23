<?php

namespace App\DataAccess\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivatniChat extends Model
{
    use HasFactory;

    protected $table = 'privatni_chat';
    protected $primaryKey = 'idChat';

    protected $fillable = [
        'idChat',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'idChat', 'idChat');
    }
}

