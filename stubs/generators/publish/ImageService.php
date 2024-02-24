<?php

namespace App\Services;

use App\Generators\GeneratorUtils;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Upload image to disk
     * @throws \Exception
     */
    public function upload(string $name, string $path, string|null $defaultImage = null, string $disk = 'local', int $width = 500, int $height = 500): string|null
    {
        if (request()->file($name) && request()->file($name)->isValid()) {
            $file = request()->file($name);

            if (str_contains(GeneratorUtils::checkPackageVersion('intervention/image-laravel'), '2')) {
                $filename = $file->hashName();
            }else{
                // set image to webp
                $filename = str()->random(20) . '.webp';
            }

            // s3 or local
            switch (config('generator.image.disk', $disk)) {
                case 's3':
                    if (str_contains(GeneratorUtils::checkPackageVersion('intervention/image-laravel'), '2')) {
                        // for intervention v2
                        $image = \Intervention\Image\Facades\Image::make($file)->resize($width, $height, config('generator.image.crop') ? function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        } : null)->encode($file->extension());

                    } else {
                        // for intervention v3
                        $imageInstance = \Intervention\Image\Laravel\Facades\Image::read($file);

                        if(config('generator.image.crop')){
                            // constraint aspect ratio
                            $image = $imageInstance->resizeDown($width, $height)->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 65));
                        }else{
                            $image = $imageInstance->resize($width, $height)->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 65));
                        }
                    }

                    Storage::disk('s3')->put($path . '/' . $filename, $image);

                    // remove old image
                    if($defaultImage) $this->delete($path . $defaultImage, 's3');
                    break;
                default:
                    if (!file_exists($path)) mkdir($path, 0777, true);

                    // for intervention v2
                    if (str_contains(GeneratorUtils::checkPackageVersion('intervention/image-laravel'), '2')) {
                        \Intervention\Image\Facades\Image::make($file->getRealPath())->resize($width, $height, config('generator.image.crop') ? function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        } : null)->save($path . $filename);
                    } else {
                        // for intervention v3
                        $image = \Intervention\Image\Laravel\Facades\Image::read($file);

                        if(config('generator.image.crop')){
                            // constraint aspect ratio
                            $image->resizeDown($width, $height)->toWebp()->save($path . $filename);
                        }else{
                            $image->resize($width, $height)->toWebp()->save($path . $filename);
                        }
                    }

                    // remove old image
                    if($defaultImage) $this->delete($path . $defaultImage);
                    break;
            }

            return $filename;
        }

        return $defaultImage;
    }

    /**
     * Remove image from disk
     */
    public function delete(string|null $image, string $disk = 'local'): bool
    {
        switch ($disk) {
            case 's3':
                Storage::disk('s3')->delete($image);
                break;
            default:
                if ($image && file_exists($image)) unlink($image);
                break;
        }

        return true;
    }
}
