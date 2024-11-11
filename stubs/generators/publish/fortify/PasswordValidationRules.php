<?php

namespace App\Actions\Fortify;

use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     */
    protected function passwordRules(): array
    {
        $validations = request()->segment(2) ? ['nullable'] : ['required', 'confirmed'];

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
