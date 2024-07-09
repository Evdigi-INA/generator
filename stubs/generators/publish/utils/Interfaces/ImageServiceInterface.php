<?php

namespace App\Generators\Interfaces;

interface ImageServiceInterface
{
    public function upload(string $name, string $path, ?string $defaultImage = null, ?string $disk = null, int $width = 500, int $height = 500, ?bool $crop = null, ?bool $aspectRatio = null, ?bool $isCustomUpload = false): ?string;

    public function delete(?string $image, string $disk = 'local'): void;
}
