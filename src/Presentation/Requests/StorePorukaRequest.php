<?php

namespace App\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePorukaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tekst' => 'required|string',
            'idChat' => 'required|exists:chat,idChat',
            'idKorisnik' => 'nullable|exists:korisnik,idKorisnik',
        ];
    }
}

