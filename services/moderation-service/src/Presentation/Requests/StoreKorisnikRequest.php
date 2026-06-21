<?php

namespace App\Presentation\Requests;

class StoreKorisnikRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:korisnik,email|max:255',
            'lozinka' => 'required|string|min:6',
            'idUloga' => 'required|exists:uloga,idUloga',
            'suspendovan' => 'nullable|date',
        ];
    }
}

