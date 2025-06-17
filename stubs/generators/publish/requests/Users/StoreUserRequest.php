<?php

namespace App\Http\Requests\Users;

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
            'email' => ['required', Rule::email(), Rule::unique(table: 'users', column: 'email')],
            'avatar' => ['nullable', 'max:1024', Rule::imageFile()],
            'role' => ['required', Rule::exists(table: 'roles', column: 'id')],
            'password' => ['required', ...$this->passwordRules()],
            'price' => ['required', Rule::numeric()],
            'date' => [Rule::date()],
            'in' => [Rule::in(values: ['foo', 'bar'])],
            // 'enum' => [Rule::enum(enum: \App\Enums\UserStatus::class)],
        ];
    }
}
