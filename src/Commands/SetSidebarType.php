<?php

namespace EvdigiIna\Generator\Commands;

use EvdigiIna\Generator\Generators\GeneratorUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

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
    protected $description = 'Configure sidebar menu type (static blade code or dynamic config-based)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $type = $this->argument(key: 'type');

        if (! $this->validateSidebarType(type: $type)) {
            return;
        }

        if (! $this->validateEnvironment()) {
            return;
        }

        $this->processSidebarType(type: $type);
    }

    protected function validateSidebarType(string $type): bool
    {
        if (! in_array(needle: $type, haystack: ['static', 'dynamic'])) {
            $this->error(string: "Invalid type. Please specify either 'static' or 'dynamic'.");

            return false;
        }

        return true;
    }

    protected function validateEnvironment(): bool
    {
        if (empty(config(key: 'generator.sidebars'))) {
            $this->error(string: 'This command requires the full version of the generator package.');
            $this->line(string: 'Please upgrade or refer to the documentation at:');
            $this->line(string: 'https://evdigi-ina.github.io/generator-docs/features/');

            return false;
        }

        return true;
    }

    protected function processSidebarType(string $type): void
    {
        $this->info(string: "Configuring {$type} sidebar...");

        $method = 'process'.Str::studly(value: $type).'Sidebar';
        $this->{$method}();

        $this->newLine();
        $this->info(string: "Sidebar successfully configured to {$type} mode!");
        $this->line(string: 'The menu will now be '.
            ($type === 'static' ? 'rendered as static blade code.' : 'dynamically loaded from config.'));
    }

    protected function processStaticSidebar(): void
    {
        $bar = $this->output->createProgressBar(max: 3);
        $bar->setFormat(format: ' %current%/%max% [%bar%] %message%');

        $bar->setMessage(message: 'Generating sidebar code...');
        $bar->start();

        $sidebarCode = $this->generateStaticSidebarCode();
        $bar->advance();

        $bar->setMessage(message: 'Preparing template...');
        $template = GeneratorUtils::replaceStub(
            replaces: ['listSidebars' => $this->cleanSidebarCode(code: $sidebarCode)],
            stubName: 'sidebar-static'
        );
        $bar->advance();

        $bar->setMessage(message: 'Writing to file...');
        file_put_contents(filename: resource_path(path: 'views/layouts/sidebar.blade.php'), data: $template);
        $bar->advance();

        $bar->finish();
    }

    protected function processDynamicSidebar(): void
    {
        $bar = $this->output->createProgressBar(1);
        $bar->setFormat(format: ' %current%/%max% [%bar%] %message%');
        $bar->setMessage(message: 'Configuring dynamic sidebar...');
        $bar->start();

        file_put_contents(
            filename: resource_path(path: 'views/layouts/sidebar.blade.php'),
            data: GeneratorUtils::getStub(path: 'sidebar-dynamic')
        );

        $bar->finish();
    }

    protected function generateStaticSidebarCode(): string
    {
        $code = '@canany([';

        foreach (config(key: 'generator.sidebars') as $sidebar) {
            if (isset($sidebar['permissions'])) {
                $code .= GeneratorUtils::convertArraySidebarToString(sidebars: $sidebar['permissions']);
            }
        }

        $code .= "])\n\t";

        foreach (config(key: 'generator.sidebars') as $sidebar) {
            if (isset($sidebar['permissions'])) {
                $code .= $this->generateSidebarHeader(sidebar: $sidebar);
                $code .= $this->generateMenuItems(menus: $sidebar['menus']);
            }
        }

        return $code."\n\t@endcanany";
    }

    protected function generateSidebarHeader(array $sidebar): string
    {
        return "
            <li class=\"sidebar-title\">{{ __(key: '{$sidebar['header']}') }}</li>
            @canany([";
    }

    protected function generateMenuItems(array $menus): string
    {
        $code = '';

        foreach ($menus as $menu) {
            $permissions = empty($menu['permission']) ? $menu['permissions'] : [$menu['permission']];
            $code .= GeneratorUtils::convertArraySidebarToString(sidebars: $permissions);
        }

        $code .= "])\n\t";

        foreach ($menus as $menu) {
            $code .= empty($menu['submenus'])
                ? $this->generateSimpleMenuItem(menu: $menu)
                : $this->generateSubmenuItem(menu: $menu);
        }

        return $code."@endcanany\n";
    }

    protected function generateSimpleMenuItem(array $menu): string
    {
        $route = str(string: $menu['route'])->remove(search: '/')->singular()->plural().'.index';

        return "
            @can('{$menu['permission']}')
                <li class=\"sidebar-item{{ is_active_menu(route: '{$menu['route']}') }}\">
                    <a href=\"{{ route(name: '{$route}') }}\" class=\"sidebar-link\">
                        {$menu['icon']}
                        <span>{{ __(key: '{$menu['title']}') }}</span>
                    </a>
                </li>
            @endcan\n";
    }

    protected function generateSubmenuItem(array $menu): string
    {
        $subPermissions = empty($menu['permission']) ? $menu['permissions'] : [$menu['permission']];
        $permissionString = GeneratorUtils::convertArraySidebarToString(sidebars: $subPermissions);

        $code = "\n@canany([{$permissionString}])
            <li class=\"sidebar-item has-sub{{ is_active_menu(route: [{$permissionString}]) }}\">
                <a href=\"#\" class=\"sidebar-link\">
                    {$menu['icon']}
                    <span>{{ __(key: '{$menu['title']}') }}</span>
                </a>
                <ul class=\"submenu\">
                @canany([{$permissionString}])";

        foreach ($menu['submenus'] as $submenu) {
            $route = str(string: $submenu['route'])->remove(search: '/')->singular()->plural().'.index';
            $code .= "
                    @can('{$submenu['permission']}')
                        <li class=\"submenu-item{{ is_active_menu(route: '{$submenu['route']}') }}\">
                            <a href=\"{{ route(name: '{$route}') }}\">{{ __(key: '{$submenu['title']}') }}</a>
                        </li>
                    @endcan";
        }

        return $code."\n\t@endcanany\n</ul>\n</li>\n@endcanany\n";
    }

    protected function cleanSidebarCode(string $code): string
    {
        return str_replace("', ]", "']", $code);
    }
}
