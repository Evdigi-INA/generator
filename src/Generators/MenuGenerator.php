<?php

namespace EvdigiIna\Generator\Generators;

class MenuGenerator
{
    /**
     * Generate a menu from a given array.
     */
    public function generate(array $request): void
    {
        if (empty($request['is_simple_generator']) && ! GeneratorUtils::isGenerateApi()) {
            $model = GeneratorUtils::setModelName(model: $request['model'], style: 'default');
            $configSidebar = config(key: 'generator.sidebars');

            if ($request['header'] == 'new') {
                $this->generateNewAllMenu(
                    request: $request,
                    model: $model,
                    configSidebar: $configSidebar
                );
            } elseif ($request['menu'] == 'new') {
                $this->generateNewMenu(
                    request: $request,
                    model: $model,
                    configSidebar: $configSidebar
                );
            } else {
                $this->generateNewSubMenu(
                    menu: json_decode(json: $request['menu'], associative: true),
                    model: $model,
                    configSidebar: $configSidebar,
                );
            }
        }
    }

    /**
     * Generate a all new sidebar menu(header, menu and submenu) on config.
     */
    protected function generateNewAllMenu(array $request, string $model, array $configSidebar): void
    {
        $newConfigSidebar = [
            'header' => GeneratorUtils::cleanPluralUcWords(string: $request['new_header']),
            'permissions' => [GeneratorUtils::cleanSingularLowerCase(string: $model).' view'],
            'menus' => [],
        ];

        $newMenu = $this->setNewMenu(
            title: GeneratorUtils::cleanPluralUcWords(string: $request['new_menu'] ?? $model),
            icon: $request['new_icon'],
            route: '/'.GeneratorUtils::pluralKebabCase(string: $model),
            submenu: $request['new_submenu'] ?? null,
            model: $model
        );

        // push new menu to new config sidebar.menu
        $newConfigSidebar['menus'][] = $newMenu;

        // push new config sidebar to old config sidebar
        $configSidebar[] = $newConfigSidebar;

        $stringCode = $this->convertJsonToArrayString(replace: $configSidebar);

        $this->generateFile(jsonToArrayString: $stringCode);
    }

    /**
     * Generate a new sidebar menu on config.
     */
    protected function generateNewMenu(array $request, string $model, array $configSidebar): void
    {
        // push to permissions on header
        $configSidebar[$request['header']]['permissions'][] = GeneratorUtils::cleanSingularLowerCase(string: $model).' view';

        // push new menu
        $configSidebar[$request['header']]['menus'][] = $this->setNewMenu(
            title: GeneratorUtils::cleanPluralUcWords(string: $request['new_menu'] ?? $model),
            icon: $request['new_icon'],
            route: '/'.GeneratorUtils::pluralKebabCase(string: $model),
            submenu: $request['new_submenu'] ?? null,
            model: $model
        );

        $stringCode = $this->convertJsonToArrayString(replace: $configSidebar);

        $this->generateFile(jsonToArrayString: $stringCode);
    }

    /**
     * Generate a new sidebar submenu on config.
     */
    protected function generateNewSubMenu(array $menu, string $model, array $configSidebar): void
    {
        $indexSidebar = $menu['sidebar'];
        $indexMenu = $menu['menus'];

        $newPermission = GeneratorUtils::cleanSingularLowerCase(string: $model).' view';

        if (count(value: $configSidebar[$indexSidebar]['menus'][$indexMenu]['submenus']) == 0) {
            // convert menu to submenu
            $configSidebar[$indexSidebar]['menus'][$indexMenu]['submenus'][] = [
                'title' => $configSidebar[$indexSidebar]['menus'][$indexMenu]['title'],
                'route' => $configSidebar[$indexSidebar]['menus'][$indexMenu]['route'],
                'permission' => $configSidebar[$indexSidebar]['menus'][$indexMenu]['permission'],
            ];

            $configSidebar[$indexSidebar]['menus'][$indexMenu]['permissions'][] = $configSidebar[$indexSidebar]['menus'][$indexMenu]['permission'];
        }

        /**
         * Push new submenu
         */
        $configSidebar[$indexSidebar]['menus'][$indexMenu]['submenus'][] = [
            'title' => GeneratorUtils::cleanPluralUcWords(string: $model),
            'route' => '/'.GeneratorUtils::pluralKebabCase(string: $model),
            'permission' => $newPermission,
        ];

        $configSidebar[$indexSidebar]['permissions'][] = $newPermission;
        $configSidebar[$indexSidebar]['menus'][$indexMenu]['route'] = null;
        $configSidebar[$indexSidebar]['menus'][$indexMenu]['permission'] = null;
        $configSidebar[$indexSidebar]['menus'][$indexMenu]['permissions'][] = $newPermission;

        $stringCode = $this->convertJsonToArrayString(replace: $configSidebar);

        $this->generateFile(jsonToArrayString: $stringCode);
    }

    /**
     * Replace code on config with newly string code.
     */
    protected function generateFile(string $jsonToArrayString): void
    {
        $stringConfig = str(string: file_get_contents(filename: config_path('generator.php')));

        if (str_contains(haystack: $stringConfig, needle: "'sidebars' => ")) {
            $search = "'sidebars' => ";
            $stringConfigCode = $stringConfig->before($search);
        } else {
            $search = '"sidebars" => ';
            $stringConfigCode = $stringConfig->before($search);
        }

        $template = "{$stringConfigCode}{$search}{$jsonToArrayString}\n];";

        file_put_contents(filename: base_path(path: 'config/generator.php'), data: $template);
    }

    /**
     * Set new menu and check if request submenu exist or not, if exist push submenu to menu.
     */
    protected function setNewMenu(string $title, string $icon, string $route, ?string $submenu, string $model): array
    {
        $newMenu = $submenu ? [
            'title' => GeneratorUtils::cleanPluralUcWords(string: $title),
            'icon' => $icon,
            'route' => null,
            'permission' => null,
            'permissions' => [GeneratorUtils::cleanSingularLowerCase(string: $model).' view'],
            'submenus' => [
                [
                    'title' => GeneratorUtils::cleanPluralUcWords(string: $submenu),
                    'route' => '/'.str(string: GeneratorUtils::pluralKebabCase(string: $model))->remove('/'),
                    'permission' => GeneratorUtils::cleanSingularLowerCase(string: $model).' view',
                ],
            ],
        ] : [
            'title' => GeneratorUtils::cleanPluralUcWords(string: $title),
            'icon' => $icon,
            'route' => '/'.str(string: GeneratorUtils::pluralKebabCase(string: $route))->remove('/'),
            'permission' => GeneratorUtils::cleanSingularLowerCase(string: $model).' view',
            'permissions' => [],
            'submenus' => [],
        ];

        return $newMenu;
    }

    /**
     * Convert json to string with format like an array.
     */
    protected function convertJsonToArrayString(array $replace): string
    {
        return str_replace(
            search: [
                '{',
                '}',
                ':',
                '"',
                "','",
                '\\',
                "='",
                "'>",
            ],
            replace: [
                '[',
                ']',
                ' =>',
                "'",
                "', '",
                '',
                '="',
                '">',
            ],
            subject: json_encode(value: $replace, flags: JSON_PRETTY_PRINT)
        );
    }
}
