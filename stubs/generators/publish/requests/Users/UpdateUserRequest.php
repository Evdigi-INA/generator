<?php

namespace App\Http\Requests\Users;

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$this->setId()],
            'avatar' => ['nullable', 'image', 'max:1024'],
            'role' => ['required', 'exists:roles,id'],
            'password' => ['nullable', ...$this->passwordRules()],
        ];
    }

    private function setId(): int|string
    {
        if (str_contains(haystack: request()->url(), needle: 'api')) {
            // /api/users/1
            return request()->segment(3);
        }

        // /users/1
        return request()->segment(2);
    }
}
