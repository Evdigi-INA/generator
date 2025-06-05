<?php

namespace App\Actions\Fortify;

use App\Generators\Services\ImageServiceV2;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function __construct(public string $avatarPath = 'avatars', public string $disk = 'storage.public')
    {
        //
    }

    /**
     * Validate and update the given user's profile information.
     */
    public function update(User $user, array $input): void
    {
        Validator::make(data: $input, rules: [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(table: 'users')->ignore(id: $user->id),
            ],
            'avatar' => ['nullable', 'image', 'max:1024'],
        ])->validateWithBag(errorBag: 'updateProfileInformation');

        if (isset($input['avatar']) && $input['avatar']->isValid()) {
            $filename = (new ImageServiceV2)->upload(name: 'avatar', path: $this->avatarPath, defaultImage: $user?->avatar);

            $user->forceFill(attributes: ['avatar' => $filename])->save();
        }

        if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser(user: $user, input: $input);
        } else {
            $user->forceFill(attributes: [
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
