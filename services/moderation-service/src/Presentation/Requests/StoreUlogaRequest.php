<?php

namespace App\Presentation\Requests;

class StoreUlogaRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'naziv' => 'required|string|max:255|unique:uloga,naziv',
        ];
    }
}

