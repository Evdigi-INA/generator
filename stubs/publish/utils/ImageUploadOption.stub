<?php

namespace App\Generators;

use Illuminate\Http\UploadedFile;

class ImageUploadOption
{
    public function __construct(
        public UploadedFile $file,
        public string $path,
        public ?string $defaultImage = null,
        public string $disk = 'storage.public',
        public int $width = 300,
        public int $height = 300,
        public bool $crop = true,
        public bool $aspectRatio = true,
        public bool $isCustomUpload = false
    ) {}
}
