<?php

namespace App\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUlogaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'naziv' => 'required|string|max:255|unique:uloga,naziv',
        ];
    }
}

