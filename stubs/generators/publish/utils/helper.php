<?php

use App\Generators\GeneratorUtils;

/**
 * Check the sidebar menu with the current Uri
 */
if (!function_exists('is_active_menu')) {
    function is_active_menu(string|array $route): string
    {
        return GeneratorUtils::isActiveMenu($route);
    }
}
