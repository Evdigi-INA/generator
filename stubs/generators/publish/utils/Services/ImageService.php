<?php

namespace App\Generators\Services;

use Illuminate\Support\Facades\Storage;
use App\Generators\Interfaces\ImageServiceInterface;

class ImageService implements ImageServiceInterface
{
    /**
     * Upload an image to disk and return the image path.
     */
    public function upload(string $name, string $path, ?string $defaultImage = null, ?string $disk = null, int $width = 500, int $height = 500, ?bool $crop = null, ?bool $aspectRatio = null, ?bool $isCustomUpload = false): ?string
    {
        $crop = $crop ?? config('generator.image.crop') ?? true;
        $aspectRatio = $aspectRatio ?? config('generator.image.aspect_ratio') ?? true;
        $disk = $disk ?? config('generator.image.disk') ?? 'storage';

        $file = request()->file($name);

        if ($file && $file->isValid()) {
            if (!$isCustomUpload) {
                return $this->handleFileUpload(file: $file, path: $path, defaultImage: $defaultImage, disk: $disk, width: $width, height: $height, crop: $crop, aspectRatio: $aspectRatio);
            } else {
                // TODO: implement custom upload
            }
        }

        return $defaultImage;
    }

    /**
     * Handle the file upload process.
     */
    private function handleFileUpload(mixed $file, string $path, ?string $defaultImage, string $disk, int $width, int $height, bool $crop, bool $aspectRatio): string
    {
        $filename = $this->generateFilename($file);

        $image = $this->processImage(file: $file, width: $width, height: $height, crop: $crop, aspectRatio: $aspectRatio);

        if ($disk === 's3') {
            Storage::disk('s3')->put($path . '/' . $filename, (string) $image, 'public');
        } else {
            $this->saveToLocal(image: $image, path: $path, filename: $filename);
        }

        if ($defaultImage) {
            $this->delete(image: $path . $defaultImage, disk: $disk);
        }

        return $filename;
    }

    /**
     * Generate a filename for the uploaded image.
     */
    private function generateFilename(mixed $file): string
    {
        if (class_exists(\Intervention\Image\Facades\Image::class)) {  // v2
            return $file->hashName();
        }

        return str()->random(30) . '.webp'; // v3
    }

    /**
     * Process the image using the appropriate version of the Intervention/Image library.
     */
    private function processImage(mixed $file, int $width, int $height, bool $crop, bool $aspectRatio): mixed
    {
        if (class_exists(\Intervention\Image\Facades\Image::class)) { // v2
            return \Intervention\Image\Facades\Image::make($file)
                ->resize($width, $height, function ($constraint) use ($crop, $aspectRatio) {
                    if ($crop) {
                        if ($aspectRatio) {
                            $constraint->aspectRatio();
                        }
                        $constraint->upsize();
                    }
                })
                ->encode('webp');
        }

        if (class_exists(\Intervention\Image\Laravel\Facades\Image::class)) { // v3
            $imageInstance = \Intervention\Image\Laravel\Facades\Image::read($file);
            $encode = new \Intervention\Image\Encoders\WebpEncoder(65);

            if ($crop) {
                if ($aspectRatio) {
                    return $imageInstance->scaleDown(width: $width, height: $height)->encode($encode);
                }

                return $imageInstance->resizeDown(width: $width, height: $height)->encode($encode);
            }

            return $imageInstance->encode($encode);
        }

        return null;
    }

    /**
     * Save the processed image to local storage.
     */
    private function saveToLocal(mixed $image, string $path, string $filename): void
    {
        if (!file_exists($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }

        if (class_exists(\Intervention\Image\Facades\Image::class)) { // v2
            $image->save($path . $filename);
        } elseif (class_exists(\Intervention\Image\Laravel\Facades\Image::class)) { // v3
            $image->save($path . $filename);
        }
    }

    /**
     * Delete an image from the specified disk.
     */
    public function delete(?string $image, string $disk = 'local'): void
    {
        if ($image) {
            if ($disk === 's3') {
                Storage::disk('s3')->delete($image);
            } else if (file_exists($image)) {
                unlink($image);
            }
        }
    }
}
