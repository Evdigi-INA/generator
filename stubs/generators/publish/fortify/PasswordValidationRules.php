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
        $validations = in_array(request()->method(), ['PUT', 'PATCH']) ? ['nullable', 'min:5'] : ['required', 'confirmed'];

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
