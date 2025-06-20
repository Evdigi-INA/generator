<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Generators\Services\ImageServiceV2;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    // use \Laravel\Sanctum\HasApiTokens;
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime:Y-m-d H:i',
            'created_at' => 'datetime:Y-m-d H:i',
            'updated_at' => 'datetime:Y-m-d H:i',
        ];
    }

    /**
     * Accessor for the 'avatar' attribute.
     */
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): string => $this->getAvatarUrl(value: $value)
        );
    }

    /**
     * Returns the URL of an 'avatar'.
     *
     * If the avatar does not exist, it returns a placeholder avatar.
     * If the avatar exists, it returns a temporary URL to the avatar on the local disk,
     * or a publicly accessible URL to the avatar on the S3 or public disk.
     * If the disk is neither local, S3, nor public, it returns a publicly accessible URL to the avatar on the local disk.
     */
    private function getAvatarUrl(?string $value): string
    {
        $path = 'avatars';
        $imageService = new ImageServiceV2;
        $disk = $imageService->setDiskName(disk: 'storage.public');

        if (! $value) {
            return $imageService->getPlaceholderImage();
        }

        return match (true) {
            $imageService->isPrivateS3(disk: $disk) || $disk === 'local' => $imageService->getTemporaryUrl(disk: $disk, image: "$path/$value"),
            in_array(needle: $disk, haystack: ['s3', 'public']) => $imageService->getStoragePublicUrl(disk: $disk, image: "$path/$value"),
            default => $imageService->getPublicAssetUrl(image: "$path/$value"),
        };
    }
}
