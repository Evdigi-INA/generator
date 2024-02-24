<?php

namespace App\Generators\Interfaces;

interface ImageServiceInterface
{
    /**
     * Upload image to disk
     * @throws \Exception
     */
    public function upload(string $name, string $path, string|null $defaultImage = null, string $disk = 'local', int $width = 500, int $height = 500): string|null;

    /**
     * Remove image from disk
     */
    public function delete(string|null $image, string $disk = 'local'): bool;
}
