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
        $validations = [];

        if (str_contains(request()->url(), 'edit')) {
            $validations[] = [
                'nullable',
            ];
        } else {
            $validations[] = [
                'required',
                'confirmed'
            ];
        }

        if (app()->isProduction()) {
            $validations[] = [
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ];
        }

        return $validations;
    }
}
