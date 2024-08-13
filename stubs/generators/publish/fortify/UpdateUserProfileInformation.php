<?php

namespace App\Actions\Fortify;

use App\Generators\Services\ImageService;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function __construct(public string $avatarPath = '')
    {
        // storage
        $this->avatarPath = storage_path('app/public/uploads/avatars/');

        // public
        // $this->avatarPath = public_path('uploads/avatars/');

        // s3
        // $this->avatarPath = '/avatars/';
    }

    /**
     * Validate and update the given user's profile information.
     */
    public function update(User $user, array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'avatar' => ['nullable', 'image', 'max:1024']
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['avatar']) && $input['avatar']->isValid()) {

            $filename = (new ImageService)->upload(name: 'avatar', path: $this->avatarPath, defaultImage: $user->avatar);

            $user->forceFill(['avatar' => $filename])->save();
        }

        if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
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
