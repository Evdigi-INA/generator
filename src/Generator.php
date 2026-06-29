<?php

namespace EvdigiIna\Generator;

use EvdigiIna\Generator\Generators\GeneratorUtils;

class Generator extends GeneratorUtils
{
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
                 * !$book->cover || $book->cover == 'https://placehold.co/300?text=No+Image+Available'"
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
                 *  if (!$generator->image == null) return 'https://placehold.co/300?text=No+Image+Available';
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
            'image' => 'https://placehold.co/300?text=No+Image+Available',
            'index_code' => 'if (!$'.self::singularCamelCase($model).'->'.str()->snake($field).") return 'https://placehold.co/300?text=No+Image+Available';",
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


}
