<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     */
    public function reset(User $user, array $input): void
    {
        Validator::make(data: $input, rules: [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill(attributes: [
            'password' => Hash::make(value: $input['password']),
        ])->save();
    }
}
