<?php

namespace App\Generators\Services;

use Illuminate\Http\UploadedFile;
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
                    'disk' => $disk ?? config(key: 'generator.image.disk', default: 'storage'),
                    'width' => $width,
                    'height' => $height,
                    'crop' => $crop ?? config(key: 'generator.image.crop', default: true),
                    'aspect_ratio' => $aspectRatio ?? config(key: 'generator.image.aspect_ratio', default: true),
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

        if ($this->isInterventionAvailable()) {
            $image = $this->processImage($options);

            switch ($options['disk']) {
                case 's3':
                    Storage::disk('s3')->put($options['path'] . '/' . $filename, (string) $image, 'public');
                    break;
                default:
                    $this->saveToLocal($image, $options['path'], $filename);
                    break;
            }

            $this->deleteOldImage(options: $options);
        } else {
            $arrPath = explode(separator: '/', string: $options['path']);

            switch ($options['disk']) {
                case 's3':
                    $path = $arrPath[1];
                    break;
                case 'storage':
                    // public path
                    if (count(value: $arrPath) == 3) {
                        $path = $arrPath[1];

                        $this->deleteOldImage(options: $options);

                        return $this->moveToPublicFolder(file: $options['file'], path: $path, filename: $filename);
                    } else {
                        $path = $arrPath[1] . '/' . $arrPath[2] . '/' . $arrPath[3];
                    }
                    break;
                default:
                    // public path
                    $path = $arrPath[1];

                    $this->deleteOldImage(options: $options);

                    return $this->moveToPublicFolder(file: $options['file'], path: $path, filename: $filename);
            }

            $this->deleteOldImage(options: $options);

            $options['file']->storeAs($path, $filename, $options['disk'] != 'storage' ? $options['disk'] : 'local');
        }

        return $filename;
    }

    private function deleteOldImage(array $options): void
    {
        if ($options['default_image']) {
            $this->delete($options['path'] . $options['default_image'], $options['disk']);
        }
    }

    /**
     * Move the uploaded file to the public folder.
     */
    private function moveToPublicFolder(UploadedFile $file, string $path, string $filename): string
    {
        $file->move(public_path("uploads/$path"), $filename);

        return $filename;
    }

    /**
     * Generate a filename for the uploaded image.
     */
    private function generateFilename(mixed $file): string
    {
        if (!$this->isInterventionAvailable()) {
            return $file->hashName();
        }

        return str()->random(30) . '.webp'; // Default extension to webp
    }

    /**
     * Process the image using Intervention/Image if available.
     */
    private function processImage(array $options): mixed
    {
        if (class_exists(class: \Intervention\Image\Facades\Image::class)) { // v2
            return \Intervention\Image\Facades\Image::make($options['file'])
                ->resize($options['width'], $options['height'], function ($constraint) use ($options): void {
                    if ($options['crop']) {
                        if ($options['aspect_ratio']) {
                            $constraint->aspectRatio();
                        }
                        $constraint->upsize();
                    }
                })
                ->encode('webp');
        }

        if (class_exists(class: \Intervention\Image\Laravel\Facades\Image::class)) { // v3
            $imageInstance = \Intervention\Image\Laravel\Facades\Image::read($options['file']);
            $encode = new \Intervention\Image\Encoders\WebpEncoder(65);

            if ($options['crop']) {
                if ($options['aspect_ratio']) {
                    return $imageInstance->scaleDown($options['width'], $options['height'])->encode($encode);
                }

                return $imageInstance->resizeDown($options['width'], $options['height'])->encode($encode);
            }

            return $imageInstance->encode($encode);
        }

        return null;
    }

    /**
     * Check if Intervention/Image is available.
     */
    private function isInterventionAvailable(): bool
    {
        return class_exists(class: \Intervention\Image\Facades\Image::class) || class_exists(class: \Intervention\Image\Laravel\Facades\Image::class);
    }

    /**
     * Save the processed image to local storage.
     */
    private function saveToLocal(mixed $image, string $path, string $filename): void
    {
        if (!file_exists(filename: $path) && !mkdir(directory: $path, permissions: 0777, recursive: true) && !is_dir(filename: $path)) {
            throw new \RuntimeException(sprintf(format: 'Directory "%s" was not created', values: $path));
        }

        $image->save($path . '/' . $filename);
    }

    /**
     * Delete an image from the specified disk.
     */
    public function delete(?string $image = null, string $disk = 'local'): void
    {
        switch ($disk) {
            case 's3':
                Storage::disk('s3')->delete($image);
                break;
            default:
                if ($image) {
                    @unlink(filename: $image);
                }
                break;
        }
    }
}
