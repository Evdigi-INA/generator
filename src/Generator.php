<?php

namespace EvdigiIna\Generator;

use EvdigiIna\Generator\Generators\GeneratorUtils;

class Generator extends GeneratorUtils
{
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
}
