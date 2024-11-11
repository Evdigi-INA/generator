<?php

namespace App\Generators\Interfaces;

interface ImageServiceInterfaceV2
{
    /**
     * Upload an image to disk and return the image name.
     */
    public function upload(
        string $name,
        string $path,
        ?string $defaultImage = null,
        string $disk = 'storage.public',
        int $width = 300,
        int $height = 300,
        bool $crop = true,
        bool $aspectRatio = true,
        bool $isCustomUpload = false
    ): ?string;

    /**
     * Deletes an image from the specified disk.
     */
    public function delete(string $path, ?string $image, string $disk = 'local'): bool;

    /**
     * Converts a disk name alias to its actual name.
     *
     * This method accepts a disk name alias and returns its actual name.
     * The supported aliases are 's3', 'storage.public' same as 'public' and
     * 'storage.local' same for 'local'. If no alias is provided, the method
     * defaults to 'public_path'.
     */
    public function setDiskName(string $disk): string;

    /**
     * Returns the default image URL to be used as a placeholder for non-existent
     * images. The default placeholder image is a 300x300 image with text
     * "No Image Available" from placehold.co.
     */
    public function getPlaceholderImage(?string $image = null): string;

    /**
     * Determines if the specified disk is a private S3 disk.
     */
    public function isPrivateS3(string $disk): bool;

    /**
     * Generates a temporary URL to a file on the specified disk.
     *
     * The URL is valid for 5 minutes.
     */
    public function getTemporaryUrl(string $disk, string $image): string;

    /**
     * Returns a publicly accessible URL to a file on the specified disk.
     *
     * This is the URL that will be used in the `<img>` tag to display the image.
     */
    public function getStoragePublicUrl(string $disk, string $image): string;

    /**
     * Returns a publicly accessible URL to a local file.
     *
     * This is the URL that will be used in the `<img>` tag to display the image.
     * The URL is generated using the Laravel `asset` helper.
     */
    public function getPublicAssetUrl(string $image): string;

    /**
     * Returns a publicly accessible URL to the specified image.
     */
    public function getImageCastUrl(?string $image, string $path, ?string $disk = 'storage.public'): string;
}
