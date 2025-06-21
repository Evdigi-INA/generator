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
        $type = $this->argument('type');

        if (! $this->validateSidebarType($type)) {
            return;
        }

        if (! $this->validateEnvironment()) {
            return;
        }

        $this->processSidebarType($type);
    }

    protected function validateSidebarType(string $type): bool
    {
        if (! in_array($type, ['static', 'dynamic'])) {
            $this->error("Invalid type. Please specify either 'static' or 'dynamic'.");

            return false;
        }

        return true;
    }

    protected function validateEnvironment(): bool
    {
        if (empty(config('generator.sidebars'))) {
            $this->error('This command requires the full version of the generator package.');
            $this->line('Please upgrade or refer to the documentation at:');
            $this->line('https://evdigi-ina.github.io/generator-docs/features/');

            return false;
        }

        if (! file_exists(resource_path('views/layouts/sidebar.blade.php'))) {
            $this->error('Sidebar view not found at: views/layouts/sidebar.blade.php');

            return false;
        }

        return true;
    }

    protected function processSidebarType(string $type): void
    {
        $this->info("Configuring {$type} sidebar...");

        $method = 'process'.Str::studly($type).'Sidebar';
        $this->{$method}();

        $this->newLine();
        $this->info("Sidebar successfully configured to {$type} mode!");
        $this->line('The menu will now be '.
            ($type === 'static' ? 'rendered as static blade code.' : 'dynamically loaded from config.'));
    }

    protected function processStaticSidebar(): void
    {
        $bar = $this->output->createProgressBar(3);
        $bar->setFormat(' %current%/%max% [%bar%] %message%');

        $bar->setMessage('Generating sidebar code...');
        $bar->start();

        $sidebarCode = $this->generateStaticSidebarCode();
        $bar->advance();

        $bar->setMessage('Preparing template...');
        $template = GeneratorUtils::replaceStub(
            ['listSidebars' => $this->cleanSidebarCode($sidebarCode)],
            'sidebar-static'
        );
        $bar->advance();

        $bar->setMessage('Writing to file...');
        file_put_contents(resource_path('views/layouts/sidebar.blade.php'), $template);
        $bar->advance();

        $bar->finish();
    }

    protected function processDynamicSidebar(): void
    {
        $bar = $this->output->createProgressBar(1);
        $bar->setFormat(' %current%/%max% [%bar%] %message%');
        $bar->setMessage('Configuring dynamic sidebar...');
        $bar->start();

        file_put_contents(
            resource_path('views/layouts/sidebar.blade.php'),
            GeneratorUtils::getStub('sidebar-dynamic')
        );

        $bar->finish();
    }

    protected function generateStaticSidebarCode(): string
    {
        $code = '@canany([';

        foreach (config('generator.sidebars') as $sidebar) {
            if (isset($sidebar['permissions'])) {
                $code .= GeneratorUtils::convertArraySidebarToString($sidebar['permissions']);
            }
        }

        $code .= "])\n\t";

        foreach (config('generator.sidebars') as $sidebar) {
            if (isset($sidebar['permissions'])) {
                $code .= $this->generateSidebarHeader($sidebar);
                $code .= $this->generateMenuItems($sidebar['menus']);
            }
        }

        return $code."\n\t@endcanany";
    }

    protected function generateSidebarHeader(array $sidebar): string
    {
        return "
            <li class=\"sidebar-title\">{{ __('{$sidebar['header']}') }}</li>
            @canany([";
    }

    protected function generateMenuItems(array $menus): string
    {
        $code = '';

        foreach ($menus as $menu) {
            $permissions = empty($menu['permission']) ? $menu['permissions'] : [$menu['permission']];
            $code .= GeneratorUtils::convertArraySidebarToString($permissions);
        }

        $code .= "])\n\t";

        foreach ($menus as $menu) {
            $code .= empty($menu['submenus'])
                ? $this->generateSimpleMenuItem($menu)
                : $this->generateSubmenuItem($menu);
        }

        return $code."@endcanany\n";
    }

    protected function generateSimpleMenuItem(array $menu): string
    {
        $route = Str::of($menu['route'])->remove('/')->singular()->plural().'.index';

        return "
            @can('{$menu['permission']}')
                <li class=\"sidebar-item{{ is_active_menu('{$menu['route']}') }}\">
                    <a href=\"{{ route('{$route}') }}\" class=\"sidebar-link\">
                        {$menu['icon']}
                        <span>{{ __('{$menu['title']}') }}</span>
                    </a>
                </li>
            @endcan\n";
    }

    protected function generateSubmenuItem(array $menu): string
    {
        $subPermissions = empty($menu['permission']) ? $menu['permissions'] : [$menu['permission']];
        $permissionString = GeneratorUtils::convertArraySidebarToString($subPermissions);

        $code = "\n@canany([{$permissionString}])
            <li class=\"sidebar-item has-sub{{ is_active_menu([{$permissionString}]) }}\">
                <a href=\"#\" class=\"sidebar-link\">
                    {$menu['icon']}
                    <span>{{ __('{$menu['title']}') }}</span>
                </a>
                <ul class=\"submenu\">
                @canany([{$permissionString}])";

        foreach ($menu['submenus'] as $submenu) {
            $route = Str::of($submenu['route'])->remove('/')->singular()->plural().'.index';
            $code .= "
                    @can('{$submenu['permission']}')
                        <li class=\"submenu-item{{ is_active_menu('{$submenu['route']}') }}\">
                            <a href=\"{{ route('{$route}') }}\">{{ __('{$submenu['title']}') }}</a>
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
