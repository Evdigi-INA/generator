<?php

namespace EvdigiIna\Generator;

use EvdigiIna\Generator\Generators\Interfaces\GeneratorUtilsInterface;
use Illuminate\Support\Facades\Schema;

class Generator implements GeneratorUtilsInterface
{
    /**
     * Get template/stub file.
     */
    public static function getStub(string $path): string
    {
        return file_get_contents(__DIR__."/../../stubs/generators/$path.stub");
    }

    /**
     * Get published files.
     */
    public static function getPublishedFiles(string $path): string
    {
        return __DIR__."/../../stubs/publish/$path";
    }

    /**
     * Check folder if not exist, then make folder.
     */
    public static function checkFolder(string $path): void
    {
        if (! file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    /**
     * Convert string to singular pascal case.
     */
    public static function singularPascalCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (self::checkStringEndWith($string)) {
            return ucfirst(str(self::fromCamelCase($string))->camel());
        }

        return ucfirst(str(self::fromCamelCase($string))->singular()->camel());
    }

    /**
     * Convert string to singular pascal case.
     */
    public static function pascalCase(string $string): string
    {
        return ucfirst(str(self::fromCamelCase($string))->camel());
    }

    /**
     * Convert string to plural pascal case.
     */
    public static function pluralPascalCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (self::checkStringEndWith($string)) {
            return ucfirst(str(self::fromCamelCase($string))->camel()).'s';
        }

        return ucfirst(str(self::fromCamelCase($string))->plural()->camel());
    }

    /**
     * Convert string to plural snake case.
     */
    public static function pluralSnakeCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (self::checkStringEndWith($string)) {
            return str(self::fromCamelCase($string))->snake()->lower().'s';
        }

        return str(self::fromCamelCase($string))->plural()->snake()->lower();
    }

    /**
     * Convert string to singular snake case.
     */
    public static function singularSnakeCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (self::checkStringEndWith($string)) {
            return str(self::fromCamelCase($string))->snake()->lower();
        }

        return str(self::fromCamelCase($string))->singular()->snake()->lower();
    }

    /**
     * Convert string to plural pascal case.
     */
    public static function pluralCamelCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (self::checkStringEndWith($string)) {
            return str(self::fromCamelCase($string))->camel().'s';
        }

        return str(self::fromCamelCase($string))->plural()->camel();
    }

    /**
     * Convert string to singular pascal case.
     */
    public static function singularCamelCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (self::checkStringEndWith($string)) {
            return str(self::fromCamelCase($string))->camel();
        }

        return str(self::fromCamelCase($string))->singular()->camel();
    }

    /**
     * Convert string to plural, kebab case, and lowercase.
     */
    public static function pluralKebabCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (self::checkStringEndWith($string)) {
            return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->kebab()->lower().'s';
        }

        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->plural()->kebab()->lower();
    }

    /**
     * Convert string to kebab case, and lowercase.
     */
    public static function kebabCase(string $string): string
    {
        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->kebab()->lower();
    }

    /**
     * Convert string to singular, kebab case, and lowercase.
     */
    public static function singularKebabCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (self::checkStringEndWith($string)) {
            return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->kebab()->lower();
        }

        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->singular()->kebab()->lower();
    }

    /**
     * Convert string to singular, remove special characters, and lowercase.
     */
    public static function cleanSingularLowerCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (self::checkStringEndWith($string)) {
            return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->lower();
        }

        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->singular()->lower();
    }

    /**
     * Remove special characters, and lowercase.
     */
    public static function cleanLowerCase(string $string): string
    {
        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->lower();
    }

    /**
     * Convert string to plural, remove special characters, and uppercase every first letters.
     */
    public static function cleanPluralUcWords(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (self::checkStringEndWith($string)) {
            return ucwords(str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->lower()).'s';
        }

        return ucwords(str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->plural()->lower());
    }

    /**
     * Convert string to singular, remove special characters, and uppercase every first letters.
     */
    public static function cleanSingularUcWords(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (self::checkStringEndWith($string)) {
            return ucwords(str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->lower());
        }

        return ucwords(str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->singular()->lower());
    }

    /**
     * Remove special characters, and uppercase every first letters.
     */
    public static function cleanUcWords(string $string): string
    {
        return ucwords(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)));
    }

    /**
     * Convert string to plural, remove special characters, and lowercase.
     */
    public static function cleanPluralLowerCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (self::checkStringEndWith($string)) {
            return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->lower().'s';
        }

        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->plural()->lower();
    }

    /**
     * Check if the given string ends with 'ia' or 'ium'.
     */
    public static function checkStringEndWith(string $string): bool
    {
        return str_ends_with($string, 'ia') || str_ends_with($string, 'ium');
    }

    /**
     * Get 1 column after id on the table.
     */
    public static function getColumnAfterId(string $table): string
    {
        $table = self::pluralSnakeCase($table);
        $allColumns = Schema::getColumnListing($table);

        if (count($allColumns) > 0) {
            $column = $allColumns[1];
        } else {
            $column = 'id';
        }

        return $column;
    }

    /**
     * Select id and column after id on the table.
     */
    public static function selectColumnAfterIdAndIdItself(string $table): string
    {
        $table = self::pluralSnakeCase($table);
        $allColumns = Schema::getColumnListing($table);

        if (count($allColumns) > 0) {
            $selectedField = "id,$allColumns[1]";
        } else {
            $selectedField = 'id';
        }

        return $selectedField;
    }

    /**
     * Get model location or path if contains '/'.
     */
    public static function getModelLocation(string $model): string
    {
        $arrModel = explode('/', $model);
        $totalArrModel = count($arrModel);

        /**
         * will generate something like:
         * Main\Product
         */
        $path = '';
        for ($i = 0; $i < $totalArrModel - 1; $i++) {
            $path .= self::pluralPascalCase($arrModel[$i]);
            if ($i + 1 != $totalArrModel - 1) {
                $path .= '\\';
            }
        }

        return $path;
    }

    /**
     * Converts camelCase string to have spaces between each.
     */
    public static function fromCamelCase(string $string): string
    {
        $a = preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/x', $string);

        return trim(implode(' ', $a));
    }

    /**
     * Set model name from the latest of array(if exists).
     */
    public static function setModelName(string $model, string $style = 'pascal case'): string
    {
        $arrModel = explode('/', $model);
        $totalArrModel = count($arrModel);

        /**
         * get the latest index value of array
         */
        $actualModelName = $arrModel[$totalArrModel - 1];

        /**
         * check string ended with 'ia' or 'ium'
         */
        if (str_ends_with($actualModelName, 'ia') || str_ends_with($actualModelName, 'ium')) {
            return self::pascalCase($actualModelName);
        }

        if ($style == 'pascal case') {
            return self::singularPascalCase($actualModelName);
        }

        return $actualModelName;
    }

    /**
     * Set default image and code to controller.
     */
    public static function setDefaultImage(?string $default, string $field, string $model): array
    {
        if ($default) {
            return [
                'image' => $default,
                /**
                 * Generated code:
                 *
                 *  if (!$generator->image || $generator->image == $defaultImage = 'https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg') return $defaultImage;
                 */
                'index_code' => 'if (!$'.self::singularCamelCase($model).'->'.str()->snake($field).' || $'.self::singularCamelCase($model).'->'.str()->snake($field)." == \$defaultImage = '".$default."') return \$defaultImage;",
                /**
                 * Generated code:
                 * !$book->cover || $book->cover == 'https://via.placeholder.com/350?text=No+Image+Avaiable'"
                 */
                'form_code' => '!$'.self::singularCamelCase($model).'->'.str()->snake($field).' || $'.self::singularCamelCase($model).'->'.str()->snake($field)." == '".$default."'",
            ];
        }

        if (config('generator.image.default')) {
            return [
                'image' => config('generator.image.default'),
                /**
                 * Generated code:
                 *
                 *  if (!$generator->image == null) return 'https://via.placeholder.com/350?text=No+Image+Avaiable';
                 */
                'index_code' => 'if (!$'.self::singularCamelCase($model).'->'.str()->snake($field).") return '".config('generator.image.default')."';",
                /**
                 * Generated code:
                 *
                 *  !$book->photo
                 */
                'form_code' => '!$'.self::singularCamelCase($model).'->'.str()->snake($field),
            ];
        }

        return [
            'image' => 'https://via.placeholder.com/350?text=No+Image+Avaiable',
            'index_code' => 'if (!$'.self::singularCamelCase($model).'->'.str()->snake($field).") return 'https://via.placeholder.com/350?text=No+Image+Avaiable';",
            'form_code' => '!$'.self::singularCamelCase($model).'->'.str()->snake($field),
        ];
    }

    /**
     * Convert array from config to string like array.
     */
    public static function convertArraySidebarToString(array $sidebars): string
    {
        $menu = '';

        foreach ($sidebars as $sidebar) {
            $menu .= "'".$sidebar."', ";
        }

        return $menu;
    }

    /**
     * Check if menu is active.
     */
    public static function isActiveMenu(string|array $route): string
    {
        $activeClass = ' active';

        if (is_string($route)) {
            if (request()->is(substr($route.'*', 1))) {
                return $activeClass;
            }

            if (request()->is(str($route)->slug().'*')) {
                return $activeClass;
            }

            if (request()->segment(2) == str($route)->before('/')) {
                return $activeClass;
            }

            if (request()->segment(3) == str($route)->after('/')) {
                return $activeClass;
            }
        }

        if (is_array($route)) {
            foreach ($route as $value) {
                $actualRoute = str($value)->remove(' view')->plural();

                if (request()->is(substr($actualRoute.'*', 1))) {
                    return $activeClass;
                }

                if (request()->is(str($actualRoute)->slug().'*')) {
                    return $activeClass;
                }

                if (request()->segment(2) == $actualRoute) {
                    return $activeClass;
                }

                if (request()->segment(3) == $actualRoute) {
                    return $activeClass;
                }
            }
        }

        return '';
    }

    /**
     * Check if generate api or blade view.
     */
    public static function isGenerateApi(): bool
    {
        return request()->filled('generate_variant') && request()->get('generate_variant') == 'api';
    }

    /**
     * Check if package exist in composer.
     */
    public static function checkPackage(string $name): bool
    {
        if (self::getComposerPackage($name) == '{') {
            return false;
        }

        return true;
    }

    /**
     * Check if package exist in composer and return version.
     *
     * @throws \Exception
     */
    public static function checkPackageVersion(string $name, bool $strict = false): string
    {
        $str = self::getComposerPackage($name);

        if (str_contains($str, '{')) {
            $message = 'The package '.$name.' is not installed.';

            if ($strict) {
                throw new \Exception($message);
            }

            return $message;
        }

        return $str;
    }

    /**
     * Check package in composer.json
     */
    public static function getComposerPackage(string $name): string
    {
        $composer = file_get_contents(base_path('composer.json'));

        return str($composer)->after('"'.$name.'": "')->before('"');
    }

    /**
     * Set disk code for controller.
     */
    public static function setDiskCodeForController(string $name): string
    {
        return match (config('generator.image.disk')) {
            // '/images/';
            's3' => "'/".self::pluralKebabCase($name)."/'",

            // 'public_path('uploads/images/');
            'public' => "public_path('uploads/".self::pluralKebabCase($name)."/')",

            // 'storage_path('app/public/uploads/images/');
            default => "storage_path('app/public/uploads/".self::pluralKebabCase($name)."/')",
        };
    }

    /**
     * Set disk code for cast an image.
     */
    public static function setDiskCodeForCastImage(string $model, string $field): string
    {
        return match (config('generator.image.disk')) {
            // \Illuminate\Support\Facades\Storage::disk('s3')->url('images/' . $generator->image);
            's3' => "\Illuminate\Support\Facades\Storage::disk('s3')->url(\$this->".self::singularCamelCase($field).'Path . $'.self::singularCamelCase($model).'->'.str($field)->snake().')',

            // asset('/uploads/photos/' . $generator->image);
            'public' => "asset('/uploads/".self::pluralKebabCase($field)."/' . $".self::singularCamelCase($model).'->'.str($field)->snake().')',

            // asset('storage/uploads/images/' . $generator->image)
            default => "asset('storage/uploads/".self::pluralKebabCase($field)."/' . $".self::singularCamelCase($model).'->'.str($field)->snake().')',
        };
    }
}
