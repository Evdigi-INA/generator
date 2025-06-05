<?php

/**
 * Check the sidebar menu with the current Uri
 */
if (! function_exists(function: 'is_active_menu')) {
    function is_active_menu(string|array $route): string
    {
        $activeClass = ' active';

        if (is_string(value: $route)) {
            if (request()->is(patterns: substr(string: "$route*", offset: 1))) {
                return $activeClass;
            }

            if (request()->is(patterns: str(string: $route)->slug().'*')) {
                return $activeClass;
            }

            if (request()->segment(index: 2) === str(string: $route)->before(search: '/')) {
                return $activeClass;
            }

            if (request()->segment(index: 3) === str(string: $route)->after(search: '/')) {
                return $activeClass;
            }
        }

        if (is_array(value: $route)) {
            foreach ($route as $value) {
                $actualRoute = str(string: $value)->remove(' view')->plural();

                if (request()->is(patterns: substr(string: "$actualRoute*", offset: 1))) {
                    return $activeClass;
                }

                if (request()->is(patterns: str(string: $actualRoute)->slug().'*')) {
                    return $activeClass;
                }

                if (request()->segment(index: 2) === $actualRoute) {
                    return $activeClass;
                }

                if (request()->segment(index: 3) === $actualRoute) {
                    return $activeClass;
                }
            }
        }

        return '';
    }
}
