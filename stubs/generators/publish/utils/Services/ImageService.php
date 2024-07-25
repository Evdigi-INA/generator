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
        $file = request()->file($name);

        if ($file && $file->isValid()) {
            if (!$isCustomUpload) {
                return $this->handleFileUpload([
                    'file' => $file,
                    'path' => $path,
                    'default_image' => $defaultImage,
                    'disk' => $disk ?? config('generator.image.disk') ?? 'storage',
                    'width' => $width,
                    'height' => $height,
                    'crop' => $crop ?? config('generator.image.crop') ?? true,
                    'aspect_ratio' => $aspectRatio ?? config('generator.image.aspect_ratio') ?? true,
                ]);
            } else {
                // TODO: implement custom upload
            }
        }

        return $defaultImage;
    }

    /**
     * Handle the file upload process.
     */
    private function handleFileUpload(array $options): string
    {
        $filename = $this->generateFilename($options['file']);

        $image = $this->processImage($options);

        if ($options['disk'] === 's3') {
            Storage::disk('s3')->put($options['path'] . '/' . $filename, (string) $image, 'public');
        } else {
            $this->saveToLocal(image: $image, path: $options['path'], filename: $filename);
        }

        if ($options['default_image']) {
            $this->delete(image: $options['path'] . $options['default_image'], disk: $options['disk']);
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
    private function processImage(array $options): mixed
    {
        if (class_exists(\Intervention\Image\Facades\Image::class)) { // v2
            return \Intervention\Image\Facades\Image::make($options['file'])
                ->resize($options['width'], $options['height'], function ($constraint) use ($options) {
                    if ($options['crop']) {
                        if ($options['aspect_ratio']) {
                            $constraint->aspectRatio();
                        }
                        $constraint->upsize();
                    }
                })
                ->encode('webp');
        }

        if (class_exists(\Intervention\Image\Laravel\Facades\Image::class)) { // v3
            $imageInstance = \Intervention\Image\Laravel\Facades\Image::read($options['file']);
            $encode = new \Intervention\Image\Encoders\WebpEncoder(65);

            if ($options['crop']) {
                if ($options['aspect_ratio']) {
                    return $imageInstance->scaleDown(width: $options['width'], height: $options['height'])->encode($encode);
                }

                return $imageInstance->resizeDown(width: $options['width'], height: $options['height'])->encode($encode);
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

        $image->save($path . $filename);
    }

    /**
     * Delete an image from the specified disk.
     */
    public function delete(string|null $image = null, string $disk = 'local'): void
    {
        switch ($disk) {
            case 's3':
                Storage::disk('s3')->delete($image);
                break;
            default:
                if ($image) {
                    @unlink($image);
                }
                break;
        }
    }
}
