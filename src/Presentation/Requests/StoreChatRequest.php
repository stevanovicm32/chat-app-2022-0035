<?php

namespace App\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idKorisnici' => 'required|array|min:1',
            'idKorisnici.*' => 'exists:korisnik,idKorisnik',
        ];
    }
}

