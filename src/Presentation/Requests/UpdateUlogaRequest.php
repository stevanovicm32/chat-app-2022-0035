<?php

namespace App\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUlogaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('uloga');
        return [
            'naziv' => 'required|string|max:255|unique:uloga,naziv,' . $id . ',idUloga',
        ];
    }
}

