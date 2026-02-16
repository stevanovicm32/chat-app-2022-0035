<?php

namespace App\Presentation\Requests;

class UpdateKorisnikRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('korisnik');
        return [
            'email' => 'sometimes|email|unique:korisnik,email,' . $id . ',idKorisnik|max:255',
            'lozinka' => 'sometimes|string|min:6',
            'idUloga' => 'sometimes|exists:uloga,idUloga',
            'suspendovan' => 'nullable|date',
        ];
    }
}

