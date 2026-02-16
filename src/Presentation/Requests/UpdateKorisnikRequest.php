<?php

namespace App\Presentation\Requests;

class UpdateKorisnikRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('korisnik');
        return [
            'email' => 'sometimes|email|unique:korisnik,email,' . $id . ',idKorisnik|max:255',
            'avatar_seed' => 'nullable|string|in:1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20',
            'lozinka' => 'sometimes|string|min:6',
            'idUloga' => 'sometimes|exists:uloga,idUloga',
            'suspendovan' => 'nullable|date',
        ];
    }
}

