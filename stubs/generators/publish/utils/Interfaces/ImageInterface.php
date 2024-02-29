<?php

namespace App\Generators\Interfaces;

interface ImageInterface
{
    public function upload(string $name, string $path, string|null $defaultImage = null, string $disk = 'local', int $width = 500, int $height = 500, bool $isCustomUpload = false): string|null;
    
    public function delete(string|null $image, string $disk = 'local'): void;
}
