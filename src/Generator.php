<?php

namespace EvdigiIna\Generator;

use EvdigiIna\Generator\Generators\Interfaces\GeneratorUtilsInterface;
use Exception;
use Illuminate\Support\Facades\Schema;

class Generator implements GeneratorUtilsInterface
{
    public static function getStub(string $path): string
    {
        return file_get_contents(__DIR__."/../../stubs/publish/$path.stub");
    }

    public static function getPublishedFiles(string $path): string
    {
        return __DIR__."/../../stubs/publish/$path";
    }

    public static function checkFolder(string $path): void
    {
        if (! file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    public static function singularPascalCase(string $string): string
    {
        if (self::checkStringEndWith($string)) {
            return ucfirst(str(self::fromCamelCase($string))->camel());
        }

        return ucfirst(str(self::fromCamelCase($string))->singular()->camel());
    }

    public static function pascalCase(string $string): string
    {
        return ucfirst(str(self::fromCamelCase($string))->camel());
    }

    public static function pluralPascalCase(string $string): string
    {
        if (self::checkStringEndWith($string)) {
            return ucfirst(str(self::fromCamelCase($string))->camel()).'s';
        }

        return ucfirst(str(self::fromCamelCase($string))->plural()->camel());
    }

    public static function pluralSnakeCase(string $string): string
    {
        if (self::checkStringEndWith($string)) {
            return str(self::fromCamelCase($string))->snake()->lower().'s';
        }

        return str(self::fromCamelCase($string))->plural()->snake()->lower();
    }

    public static function singularSnakeCase(string $string): string
    {
        if (self::checkStringEndWith($string)) {
            return str(self::fromCamelCase($string))->snake()->lower();
        }

        return str(self::fromCamelCase($string))->singular()->snake()->lower();
    }

    public static function pluralCamelCase(string $string): string
    {
        if (self::checkStringEndWith($string)) {
            return str(self::fromCamelCase($string))->camel().'s';
        }

        return str(self::fromCamelCase($string))->plural()->camel();
    }

    public static function singularCamelCase(string $string): string
    {
        if (self::checkStringEndWith($string)) {
            return str(self::fromCamelCase($string))->camel();
        }

        return str(self::fromCamelCase($string))->singular()->camel();
    }

    public static function pluralKebabCase(string $string): string
    {
        if (self::checkStringEndWith($string)) {
            return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->kebab()->lower().'s';
        }

        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->plural()->kebab()->lower();
    }

    public static function kebabCase(string $string): string
    {
        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->kebab()->lower();
    }

    public static function singularKebabCase(string $string): string
    {
        if (self::checkStringEndWith($string)) {
            return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->kebab()->lower();
        }

        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->singular()->kebab()->lower();
    }

    public static function cleanSingularLowerCase(string $string): string
    {
        if (self::checkStringEndWith($string)) {
            return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->lower();
        }

        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->singular()->lower();
    }

    public static function cleanLowerCase(string $string): string
    {
        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->lower();
    }

    public static function cleanPluralUcWords(string $string): string
    {
        if (self::checkStringEndWith($string)) {
            return ucwords(str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->lower()).'s';
        }

        return ucwords(str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->plural()->lower());
    }

    public static function cleanSingularUcWords(string $string): string
    {
        if (self::checkStringEndWith($string)) {
            return ucwords(str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->lower());
        }

        return ucwords(str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->singular()->lower());
    }

    public static function cleanUcWords(string $string): string
    {
        return ucwords(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)));
    }

    public static function cleanPluralLowerCase(string $string): string
    {
        if (self::checkStringEndWith($string)) {
            return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->lower().'s';
        }

        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', self::fromCamelCase($string)))->plural()->lower();
    }

    public static function checkStringEndWith(string $string): bool
    {
        return str_ends_with($string, 'ia') || str_ends_with($string, 'ium');
    }

    public static function getColumnAfterId(string $table): string
    {
        $table = self::pluralSnakeCase($table);
        $allColumns = Schema::getColumnListing($table);

        return count($allColumns) > 0 ? $allColumns[1] : 'id';
    }

    public static function selectColumnAfterIdAndIdItself(string $table): string
    {
        $table = self::pluralSnakeCase($table);
        $allColumns = Schema::getColumnListing($table);

        return count($allColumns) > 0 ? "id,$allColumns[1]" : 'id';
    }

    public static function getModelLocation(string $model): string
    {
        $arrModel = explode('/', $model);
        $totalArrModel = count($arrModel);

        $path = '';
        for ($i = 0; $i < $totalArrModel - 1; $i++) {
            $path .= self::pluralPascalCase($arrModel[$i]);
            if ($i + 1 != $totalArrModel - 1) {
                $path .= '\\';
            }
        }

        return $path;
    }

    public static function fromCamelCase(string $string): string
    {
        $a = preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/x', $string);

        return trim(implode(' ', $a));
    }

    public static function setModelName(string $model, string $style = 'pascal case'): string
    {
        $arrModel = explode('/', $model);
        $actualModelName = $arrModel[count($arrModel) - 1];

        if (str_ends_with($actualModelName, 'ia') || str_ends_with($actualModelName, 'ium')) {
            return self::pascalCase($actualModelName);
        }

        return $style == 'pascal case'
            ? self::singularPascalCase($actualModelName)
            : $actualModelName;
    }

    public static function setDefaultImage(?string $default, string $field, string $model): array
    {
        if ($default) {
            return [
                'image' => $default,
                'index_code' => 'if (!$'.self::singularCamelCase($model).'->'.str()->snake($field).' || $'.self::singularCamelCase($model).'->'.str()->snake($field)." == \$defaultImage = '".$default."') return \$defaultImage;",
                'form_code' => '!$'.self::singularCamelCase($model).'->'.str()->snake($field).' || $'.self::singularCamelCase($model).'->'.str()->snake($field)." == '".$default."'",
            ];
        }

        if (config('generator.image.default')) {
            return [
                'image' => config('generator.image.default'),
                'index_code' => 'if (!$'.self::singularCamelCase($model).'->'.str()->snake($field).") return '".config('generator.image.default')."';",
                'form_code' => '!$'.self::singularCamelCase($model).'->'.str()->snake($field),
            ];
        }

        return [
            'image' => 'https://placehold.co/300?text=No+Image+Available',
            'index_code' => 'if (!$'.self::singularCamelCase($model).'->'.str()->snake($field).") return 'https://placehold.co/300?text=No+Image+Available';",
            'form_code' => '!$'.self::singularCamelCase($model).'->'.str()->snake($field),
        ];
    }

    public static function convertArraySidebarToString(array $sidebars): string
    {
        return implode("', '", $sidebars);
    }

    public static function isActiveMenu(string|array $route): string
    {
        $activeClass = ' active';

        if (is_string($route)) {
            if (request()->is(substr($route.'*', 1)) ||
                request()->is(str($route)->slug().'*') ||
                request()->segment(2) == str($route)->before('/') ||
                request()->segment(3) == str($route)->after('/')
            ) {
                return $activeClass;
            }
        }

        if (is_array($route)) {
            foreach ($route as $value) {
                $actualRoute = str($value)->remove(' view')->plural();

                if (request()->is(substr($actualRoute.'*', 1)) ||
                    request()->is(str($actualRoute)->slug().'*') ||
                    request()->segment(2) == $actualRoute ||
                    request()->segment(3) == $actualRoute
                ) {
                    return $activeClass;
                }
            }
        }

        return '';
    }

    public static function isGenerateApi(): bool
    {
        return request()->filled('generate_variant') && request()->get('generate_variant') == 'api';
    }

    public static function checkPackage(string $name): bool
    {
        return self::getComposerPackage($name) != '{';
    }

    public static function checkPackageVersion(string $name, bool $strict = false): string
    {
        $str = self::getComposerPackage($name);

        if (str_contains($str, '{')) {
            $message = 'The package '.$name.' is not installed.';

            if ($strict) {
                throw new Exception($message);
            }

            return $message;
        }

        return $str;
    }

    public static function getComposerPackage(string $name): string
    {
        $composer = file_get_contents(base_path('composer.json'));

        return str($composer)->after('"'.$name.'": "')->before('"');
    }

    public static function setDiskCodeForController(string $name): string
    {
        return match (config('generator.image.disk')) {
            's3' => "'/".self::pluralKebabCase($name)."/'",
            'public' => "public_path('uploads/".self::pluralKebabCase($name)."/')",
            default => "storage_path('app/public/uploads/".self::pluralKebabCase($name)."/')",
        };
    }

    public static function setDiskCodeForCastImage(string $model, string $field): string
    {
        return match (config('generator.image.disk')) {
            's3' => "\Illuminate\Support\Facades\Storage::disk('s3')->url(\$this->".self::singularCamelCase($field).'Path . $'.self::singularCamelCase($model).'->'.str($field)->snake().')',
            'public' => "asset('/uploads/".self::pluralKebabCase($field)."/' . $".self::singularCamelCase($model).'->'.str($field)->snake().')',
            default => "asset('storage/uploads/".self::pluralKebabCase($field)."/' . $".self::singularCamelCase($model).'->'.str($field)->snake().')',
        };
    }
}
