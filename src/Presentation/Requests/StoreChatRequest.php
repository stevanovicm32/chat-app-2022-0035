<?php

namespace App\Presentation\Requests;

class StoreChatRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'idKorisnici' => 'required|array|min:1',
            'idKorisnici.*' => 'exists:korisnik,idKorisnik',
        ];
    }
}

