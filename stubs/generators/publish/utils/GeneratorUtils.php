<?php

namespace EvdigiIna\Generator\Generators;

use EvdigiIna\Generator\Generators\Interfaces\GeneratorUtilsInterface;
use Illuminate\Support\Facades\Schema;

class GeneratorUtils implements GeneratorUtilsInterface
{
    /**
     * Get template/stub file.
     */
    public static function getStub(string $path): string
    {
        return file_get_contents(__DIR__ . "/../../stubs/generators/$path.stub");
    }

    /**
     * Get published files.
     */
    public static function getPublishedFiles(string $path): string
    {
        return __DIR__ . "/../../stubs/publish/$path";
    }

    /**
     * Check folder if not exist, then make folder.
     */
    public static function checkFolder(string $path): void
    {
        if (!file_exists($path)) {
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
        if (substr($string, -2) == 'ia' || substr($string, -3) == 'ium') {
            return ucfirst(str(GeneratorUtils::fromCamelCase($string))->camel());
        }

        return ucfirst(str(GeneratorUtils::fromCamelCase($string))->singular()->camel());
    }

    /**
     * Convert string to singular pascal case.
     */
    public static function pascalCase(string $string): string
    {
        return ucfirst(str(GeneratorUtils::fromCamelCase($string))->camel());
    }

    /**
     * Convert string to plural pascal case.
     */
    public static function pluralPascalCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (substr($string, -2) == 'ia' || substr($string, -3) == 'ium') {
            return ucfirst(str(GeneratorUtils::fromCamelCase($string))->camel()) . 's';
        }

        return ucfirst(str(GeneratorUtils::fromCamelCase($string))->plural()->camel());
    }

    /**
     * Convert string to plural snake case.
     */
    public static function pluralSnakeCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (substr($string, -2) == 'ia' || substr($string, -3) == 'ium') {
            return str(GeneratorUtils::fromCamelCase($string))->snake()->lower() . 's';
        }

        return str(GeneratorUtils::fromCamelCase($string))->plural()->snake()->lower();
    }

    /**
     * Convert string to singular snake case.
     */
    public static function singularSnakeCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (substr($string, -2) == 'ia' || substr($string, -3) == 'ium') {
            return str(GeneratorUtils::fromCamelCase($string))->snake()->lower();
        }

        return str(GeneratorUtils::fromCamelCase($string))->singular()->snake()->lower();
    }

    /**
     * Convert string to plural pascal case.
     */
    public static function pluralCamelCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (substr($string, -2) == 'ia' || substr($string, -3) == 'ium') {
            return str(GeneratorUtils::fromCamelCase($string))->camel() . 's';
        }

        return str(GeneratorUtils::fromCamelCase($string))->plural()->camel();
    }

    /**
     * Convert string to singular pascal case.
     */
    public static function singularCamelCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (substr($string, -2) == 'ia' || substr($string, -3) == 'ium') {
            return str(GeneratorUtils::fromCamelCase($string))->camel();
        }

        return str(GeneratorUtils::fromCamelCase($string))->singular()->camel();
    }

    /**
     * Convert string to plural, kebab case, and lowercase.
     */
    public static function pluralKebabCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (substr($string, -2) == 'ia' || substr($string, -3) == 'ium') {
            return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->kebab()->lower() . 's';
        }

        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->plural()->kebab()->lower();
    }

    /**
     * Convert string to kebab case, and lowercase.
     */
    public static function kebabCase(string $string): string
    {
        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->kebab()->lower();
    }

    /**
     * Convert string to singular, kebab case, and lowercase.
     */
    public static function singularKebabCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (substr($string, -2) == 'ia' || substr($string, -3) == 'ium') {
            return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->kebab()->lower();
        }

        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->singular()->kebab()->lower();
    }

    /**
     * Convert string to singular, remove special characters, and lowercase.
     */
    public static function cleanSingularLowerCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (substr($string, -2) == 'ia' || substr($string, -3) == 'ium') {
            return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->lower();
        }

        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->singular()->lower();
    }

    /**
     * Remove special characters, and lowercase.
     */
    public static function cleanLowerCase(string $string): string
    {
        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->lower();
    }

    /**
     * Convert string to plural, remove special characters, and uppercase every first letters.
     */
    public static function cleanPluralUcWords(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (substr($string, -2) == 'ia' || substr($string, -3) == 'ium') {
            return ucwords(str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->lower()) . 's';
        }

        return ucwords(str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->plural()->lower());
    }

    /**
     * Convert string to singular, remove special characters, and uppercase every first letters.
     */
    public static function cleanSingularUcWords(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (substr($string, -2) == 'ia' || substr($string, -3) == 'ium') {
            return ucwords(str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->lower());
        }

        return ucwords(str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->singular()->lower());
    }

    /**
     * Remove special characters, and uppercase every first letters.
     */
    public static function cleanUcWords(string $string): string
    {
        return ucwords(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)));
    }

    /**
     * Convert string to plural, remove special characters, and lowercase.
     */
    public static function cleanPluralLowerCase(string $string): string
    {
        /**
         * check string ended with 'ia' or 'ium'
         */
        if (substr($string, -2) == 'ia' || substr($string, -3) == 'ium') {
            return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->lower() . 's';
        }

        return str(preg_replace('/[^A-Za-z0-9() -]/', ' ', GeneratorUtils::fromCamelCase($string)))->plural()->lower();
    }

    /**
     * Get 1 column after id on the table.
     */
    public static function getColumnAfterId(string $table): string
    {
        $table = GeneratorUtils::pluralSnakeCase($table);
        $allColumns = Schema::getColumnListing($table);

        if (sizeof($allColumns) > 0) {
            $column = $allColumns[1];
        } else {
            $column = "id";
        }

        return $column;
    }

    /**
     * Select id and column after id on the table.
     */
    public static function selectColumnAfterIdAndIdItself(string $table): string
    {
        $table = GeneratorUtils::pluralSnakeCase($table);
        $allColumns = Schema::getColumnListing($table);

        if (sizeof($allColumns) > 0) {
            $selectedField = "id,$allColumns[1]";
        } else {
            $selectedField = "id";
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
        $path = "";
        for ($i = 0; $i < $totalArrModel - 1; $i++) {
            $path .= GeneratorUtils::pluralPascalCase($arrModel[$i]);
            if ($i + 1 != $totalArrModel - 1) {
                $path .= "\\";
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
        if (substr($actualModelName, -2) == 'ia' || substr($actualModelName, -3) == 'ium') {
            return self::pascalCase($actualModelName);
        }

        if ($style == 'pascal case') {
            return GeneratorUtils::singularPascalCase($actualModelName);
        }

        return $actualModelName;
    }

    /**
     * Set default image and code to controller.
     */
    public static function setDefaultImage(null|string $default, string $field, string $model): array
    {
        if ($default) {
            return [
                'image' => $default,
                /**
                 * Generated code:
                 *
                 *  if ($row->photo == null || $row->photo == $defaultImage = 'https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg') {
                 *      return $defaultImage;
                 */
                'index_code' => "if (\$row->" . str()->snake($field) . " == null || \$row->" . str()->snake($field) . " == \$defaultImage = '" . $default . "') {
                    return \$defaultImage;
                }",
                /**
                 * Generated code:
                 * $book->cover == null || $book->cover == 'https://via.placeholder.com/350?text=No+Image+Avaiable'"
                 */
                'form_code' => "$" . GeneratorUtils::singularCamelCase($model) . "->" . str()->snake($field) . " == null || $" . GeneratorUtils::singularCamelCase($model) . "->" . str()->snake($field) . " == '" . $default . "'",
            ];
        }

        if (config('generator.image.default')) {
            return [
                'image' => config('generator.image.default'),
                /**
                 * Generated code:
                 *
                 *  if ($row->photo == null) {
                 *      return 'https://via.placeholder.com/350?text=No+Image+Avaiable';
                 */
                'index_code' => "if (\$row->" . str()->snake($field) . " == null) {
                    return '" . config('generator.image.default')  . "';
                }",
                /**
                 * Generated code:
                 *
                 *  $book->photo == null
                 */
                'form_code' => "$" . GeneratorUtils::singularCamelCase($model) . "->" . str()->snake($field) . " == null",
            ];
        }

        return [
            'image' => 'https://via.placeholder.com/350?text=No+Image+Avaiable',
            'index_code' => "if (\$row->" . str()->snake($field) . " == null) {
                return 'https://via.placeholder.com/350?text=No+Image+Avaiable';
            }",
            'form_code' => "$" . GeneratorUtils::singularCamelCase($model) . "->" . str()->snake($field) . " == null",
        ];
    }

    /**
     * Convert array from config to string like array.
     */
    public static function convertArraySidebarToString(array $sidebars): string
    {
        $menu = "";

        foreach ($sidebars as $sidebar) {
            $menu .= "'" . $sidebar . "', ";
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
            if (request()->is(substr($route . '*', 1))) {
                return $activeClass;
            }

            if (request()->is(str($route)->slug() . '*')) {
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

                if (request()->is(substr($actualRoute . '*', 1))) {
                    return $activeClass;
                }

                if (request()->is(str($actualRoute)->slug() . '*')) {
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
     * Check if generate blade(default) or api.
     */
    public static function isGenerateApi(): bool
    {
        return request()->filled('generate_variant') && request()->get('generate_variant') == 'api';
    }
}
