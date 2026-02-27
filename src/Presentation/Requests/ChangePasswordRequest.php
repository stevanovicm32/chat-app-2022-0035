<?php

namespace App\Presentation\Requests;

class ChangePasswordRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'staraLozinka' => 'required|string',
            'novaLozinka' => 'required|string|min:6|confirmed',
        ];
    }

    public function attributes(): array
    {
        return [
            'novaLozinka' => 'nova lozinka',
        ];
    }
}

