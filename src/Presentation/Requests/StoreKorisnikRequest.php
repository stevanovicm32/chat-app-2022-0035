<?php

namespace App\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKorisnikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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

