<?php

namespace App\Generators\Interfaces;

interface ImageServiceInterfaceV2
{
    public function upload(string $name, string $path, ?string $defaultImage = null, ?string $disk = null, int $width = 300, int $height = 300, ?bool $crop = null, ?bool $aspectRatio = null, ?bool $isCustomUpload = false): ?string;

    public function delete(string $path, ?string $image, string $disk = 'local'): bool;

    public function setDiskName(string $disk): string;
}
