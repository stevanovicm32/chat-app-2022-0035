<?php

namespace App\Presentation\Requests;

class UpdateUlogaRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->route('uloga');
        return [
            'naziv' => 'required|string|max:255|unique:uloga,naziv,' . $id . ',idUloga',
        ];
    }
}

