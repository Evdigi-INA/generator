<?php

namespace EvdigiIna\Generator\Generators;

class MenuGenerator
{
    /**
     * Generate a menu from a given array.
     */
    public function generate(array $request): void
    {
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $configSidebar = config('generator.sidebars');

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
                menu: json_decode($request['menu'], true),
                model: $model,
                configSidebar: $configSidebar,
            );
        }
    }

    /**
     * Generate a all new sidebar menu(header, menu and submenu) on config.
     */
    protected function generateNewAllMenu(array $request, string $model, array $configSidebar): void
    {
        $newConfigSidebar = [
            'header' => GeneratorUtils::cleanPluralUcWords($request['new_header']),
            'permissions' => [GeneratorUtils::cleanSingularLowerCase($model) . ' view'],
            'menus' => [],
        ];

        $newMenu = $this->setNewMenu(
            title: GeneratorUtils::cleanPluralUcWords($model),
            icon: $request['new_icon'],
            route: '/' . GeneratorUtils::pluralKebabCase($model),
            submenu: isset($request['new_submenu']) ? $request['new_submenu'] : null
        );

        // push new menu to new config sidebar.menu
        $newConfigSidebar['menus'][] = $newMenu;

        // push new config sidebar to old config sidebar
        $configSidebar[] = $newConfigSidebar;

        $stringCode = $this->convertJsonToArrayString($configSidebar);

        $this->generateFile($stringCode);
    }

    /**
     * Generate a new sidebar menu on config.
     */
    protected function generateNewMenu(array $request, string $model, array $configSidebar): void
    {
        // push to permissions on header
        $configSidebar[$request['header']]['permissions'][] = GeneratorUtils::cleanSingularLowerCase($model) . ' view';

        // push new menu
        $configSidebar[$request['header']]['menus'][] = $this->setNewMenu(
            title: GeneratorUtils::cleanPluralUcWords($model),
            icon: $request['new_icon'],
            route: '/' . GeneratorUtils::pluralKebabCase($model),
            submenu: $request['new_submenu'] ?? null
        );

        $stringCode = $this->convertJsonToArrayString($configSidebar);

        $this->generateFile($stringCode);
    }

    /**
     * Generate a new sidebar submenu on config.
     */
    protected function generateNewSubMenu(array $menu, string $model, array $configSidebar): void
    {
        $indexSidebar = $menu['sidebar'];
        $indexMenu = $menu['menus'];

        $newPermission = GeneratorUtils::cleanSingularLowerCase($model) . ' view';

        /**
         * Push to permissions on header
         */
        $configSidebar[$indexSidebar]['permissions'][] = $newPermission;

        /**
         * If submenus[] is empty, move menu at this index into the submenus[] and make route and permission to null
         */
        if ($configSidebar[$indexSidebar]['menus'][$indexMenu]['submenus'] == []) {
            $configSidebar[$indexSidebar]['menus'][$indexMenu]['submenus'][] = [
                'title' => $configSidebar[$indexSidebar]['menus'][$indexMenu]['title'],
                'route' => $configSidebar[$indexSidebar]['menus'][$indexMenu]['route'],
                'permission' => $configSidebar[$indexSidebar]['menus'][$indexMenu]['permission'],
            ];

            /**
             * Push to permissions on menus
             */
            $configSidebar[$indexSidebar]['menus'][$indexMenu]['permissions'][] = $configSidebar[$indexSidebar]['menus'][$indexMenu]['permission'] . "', '" . $newPermission;

            $configSidebar[$indexSidebar]['menus'][$indexMenu]['route'] = null;
            $configSidebar[$indexSidebar]['menus'][$indexMenu]['permission'] = null;
        } else {
            $configSidebar[$indexSidebar]['menus'][$indexMenu]['permissions'][] = $newPermission;
        }

        /**
         * Push new submenu
         */
        $configSidebar[$indexSidebar]['menus'][$indexMenu]['submenus'][] = [
            'title' => GeneratorUtils::cleanPluralUcWords($model),
            'route' => '/' . GeneratorUtils::pluralKebabCase($model),
            'permission' => GeneratorUtils::cleanSingularLowerCase($model) . ' view',
        ];

        $stringCode = $this->convertJsonToArrayString($configSidebar);

        $this->generateFile($stringCode);
    }

    /**
     * Replace code on config with newly string code.
     */
    protected function generateFile(string $jsonToArrayString): void
    {
        $stringConfig = str(file_get_contents(config_path('generator.php')));

        if(str_contains($stringConfig, "'sidebars' => ")){
            $search = "'sidebars' => ";
            $stringConfigCode = $stringConfig->before($search);
        }else{
            $search = '"sidebars" => ';
            $stringConfigCode = $stringConfig->before($search);
        }

        $template = $stringConfigCode . $search . $jsonToArrayString . "\n];";

        file_put_contents(base_path('config/generator.php'), $template);
    }

    /**
     * Set new menu and check if request submenu exist or not, if exist push submenu to menu.
     */
    protected function setNewMenu(string $title, string $icon, string $route, string|null $submenu = null): array
    {
        if (isset($submenu)) {
            $newMenu = [
                'title' => GeneratorUtils::cleanPluralUcWords($title),
                'icon' => $icon,
                'route' => null,
                'permission' => null,
                'permissions' => [GeneratorUtils::cleanSingularLowerCase($submenu) . ' view'],
                'submenus' => [
                    [
                        'title' =>  GeneratorUtils::cleanPluralUcWords($submenu),
                        'route' => '/' . str(GeneratorUtils::pluralKebabCase($submenu))->remove('/'),
                        'permission' => GeneratorUtils::cleanSingularLowerCase($submenu) . ' view',
                    ]
                ]
            ];
        } else {
            $newMenu = [
                'title' => GeneratorUtils::cleanPluralUcWords($title),
                'icon' => $icon,
                'route' => '/' . str(GeneratorUtils::pluralKebabCase($route))->remove('/'),
                'permission' => GeneratorUtils::cleanSingularLowerCase($title) . ' view',
                'permissions' => [],
                'submenus' =>  []
            ];
        }

        return $newMenu;
    }

    /**
     * Convert json to string with format like an array.
     */
    protected function convertJsonToArrayString(array $replace): string
    {
        return str_replace(
            [
                '{',
                '}',
                ':',
                '"',
                "','",
                "\\",
                "='",
                "'>",
            ],
            [
                '[',
                ']',
                ' =>',
                "'",
                "', '",
                '',
                '="',
                '">',
            ],
            json_encode($replace, JSON_PRETTY_PRINT)
        );
    }
}
