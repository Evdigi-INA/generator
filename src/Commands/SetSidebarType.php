<?php

namespace EvdigiIna\Generator\Commands;

use EvdigiIna\Generator\Generators\GeneratorUtils;
use Illuminate\Console\Command;

class SetSidebarType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:sidebar {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set a sidebar menu to fully blade code(static) or use a list from config(dynamic)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        switch ($this->argument('type')) {
            case 'static':
                $this->checkGeneratorVariant();

                $sidebarCode = '';
                $sidebarCode .= '@canany([';

                foreach (config(key: 'generator.sidebars') as $sidebar) {
                    if (isset($sidebar['permissions'])) {
                        $sidebarCode .= GeneratorUtils::convertArraySidebarToString(sidebars: $sidebar['permissions']);
                    }
                }

                $sidebarCode .= "])\n\t";

                foreach (config(key: 'generator.sidebars') as $sidebar) {
                    if (isset($sidebar['permissions'])) {
                        $sidebarCode .= "
                            <li class=\"sidebar-title\">{{ __('".$sidebar['header']."') }}</li>
                            @canany([";

                        foreach ($sidebar['menus'] as $menu) {
                            $permissions = empty($menu['permission']) ? $menu['permissions'] : [$menu['permission']];
                            $sidebarCode .= GeneratorUtils::convertArraySidebarToString(sidebars: $permissions);
                        }

                        $sidebarCode .= "])\n\t";

                        foreach ($sidebar['menus'] as $menu) {
                            $subPermissions = empty($menu['permission']) ? $menu['permissions'] : [$menu['permission']];

                            switch (empty($menu['submenus'])) {
                                case true:
                                    $sidebarCode .= "
                                    @can('".$menu['permission']."')
                                        <li class=\"sidebar-item{{ is_active_menu('".$menu['route']."') }}\">
                                        <a href=\"{{ route('".str(string: $menu['route'])->remove('/')->singular()->plural().'.index'."') }}\" class=\"sidebar-link\">
                                                ".$menu['icon']."
                                                <span>{{ __('".$menu['title']."') }}</span>
                                            </a>
                                        </li>
                                    @endcan\n";
                                    break;

                                case false:
                                    $sidebarCode .= "\n@canany([".GeneratorUtils::convertArraySidebarToString(sidebars: $subPermissions).'])<li class="sidebar-item has-sub{{  is_active_menu(['.GeneratorUtils::convertArraySidebarToString(sidebars: $subPermissions).']) }}">
                                    <a href="#" class="sidebar-link">
                                        '.$menu['icon']."
                                        <span>{{ __('".$menu['title']."') }}</span>
                                    </a>
                                    <ul class=\"submenu\">
                                    @canany([".GeneratorUtils::convertArraySidebarToString(sidebars: $subPermissions).'])';

                                    foreach ($menu['submenus'] as $submenu) {
                                        $sidebarCode .= "
                                        @can('".$submenu['permission']."')
                                            <li class=\"submenu-item{{ is_active_menu('".$submenu['route']."') }}\">
                                            <a href=\"{{ route('".str(string: $submenu['route'])->remove('/')->singular()->plural().'.index'."') }}\">{{ __('".$submenu['title']."') }}</a>
                                            </li>
                                        @endcan\n";
                                    }

                                    $sidebarCode .= "\n\t@endcanany\n</ul>\n\t</li>\n@endcanany\n";
                                    break;
                            }

                        }
                        $sidebarCode .= "@endcanany\n";
                    }
                }

                $sidebarCode .= "\n\t@endcanany";

                $sidebarCodeReplaced = str_replace(
                    search: "', ]",
                    replace: "']",
                    subject: $sidebarCode
                );

                $template = GeneratorUtils::replaceStub(
                    replaces: [
                        'listSidebars' => $sidebarCodeReplaced,
                    ],
                    stubName: 'sidebar-static'
                );

                file_put_contents(filename: resource_path(path: 'views/layouts/sidebar.blade.php'), data: $template);

                $this->info('You have successfully switched to full blade code in the sidebar view.');
                break;
            case 'dynamic':
                $this->checkGeneratorVariant();

                file_put_contents(filename: resource_path(path: 'views/layouts/sidebar.blade.php'), data: GeneratorUtils::getStub(path: 'sidebar-dynamic'));

                $this->info('A dynamic list from the config(config.generator) was used in the sidebar.');
                break;
            default:
                $this->error("The type must be 'static' or 'dynamic'.");
                break;
        }
    }

    /**
     * Check if user using the simple version or not.
     */
    public function checkGeneratorVariant(): void
    {
        if (empty(config('generator.sidebars'))) {
            $this->error('It looks that you are using the simple version, this command is only available in the full version. Please refer to the section on available commands at https://evdigi-ina.github.io/generator-docs/features/');

            return;
        }

        $sidebar = file_exists(resource_path('views/layouts/sidebar.blade.php'));

        if (! $sidebar) {
            $this->error('We cant find the sidebar view, in views/layouts/sidebar.blade.php.');

            return;
        }
    }
}
