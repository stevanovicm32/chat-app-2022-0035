<?php

namespace App\Presentation\Requests;

class StorePorukaRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->filled('idChat') && $this->route('chat')) {
            $chatParam = $this->route('chat');
            $resolvedId = $chatParam instanceof \App\DataAccess\Models\Chat ? $chatParam->idChat : $chatParam;
            $this->merge(['idChat' => $resolvedId]);
        }

        if (!$this->filled('idKorisnik') && $this->user()) {
            $this->merge(['idKorisnik' => $this->user()->idKorisnik]);
        }
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

