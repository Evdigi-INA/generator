<?php

namespace App\Actions\Fortify;

// use Laravel\Fortify\Rules\Password;
use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     */
    protected function passwordRules(): array
    {
        if(strtolower(env('APP_ENV')) === 'production') {
            return [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ];
        }

        return [
            'required',
            'string',
            'confirmed',
        ];
    }
}
