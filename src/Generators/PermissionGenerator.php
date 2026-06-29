<?php

namespace EvdigiIna\Generator\Generators;


use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionGenerator
{
    /**
     * Generate new permissions to config.permissions.permissions(used for permissions seeder).
     */
    public function generate(array $request): void
    {
        if (empty($request['is_simple_generator'])) {
            $model = GeneratorUtils::setModelName(model: $request['model'], style: 'default');
            $modelNamePlural = GeneratorUtils::cleanPluralLowerCase(string: $model);
            $modelNameSingular = GeneratorUtils::cleanSingularLowerCase(string: $model);

            $stringPermissions = str_replace(
                search: [
                    '{',
                    '}',
                    ':',
                    '"',
                    ',',
                    ']]',
                ],
                replace: [
                    '[',
                    ']',
                    ' => ',
                    "'",
                    ', ',
                    "]], \n\t\t",
                ],
                subject: json_encode(value: [
                    'group' => $modelNamePlural,
                    'access' => [
                        "$modelNameSingular view",
                        "$modelNameSingular create",
                        "$modelNameSingular edit",
                        "$modelNameSingular delete",
                    ],
                ])
            );

            $path = config_path('permission.php');

            $content = file_get_contents(filename: $path);
            $lastClosing = strrpos(haystack: $content, needle: '];');
            $newPermissionFile = substr(string: $content, offset: 0, length: $lastClosing).$stringPermissions.'],];';

            file_put_contents(filename: $path, data: $newPermissionFile);

            $this->insertRoleAndPermissions(model: $modelNameSingular);
        }
    }

    /**
     * Insert new role & permissions then give an admin that permissions.
     */
    protected function insertRoleAndPermissions(string $model): void
    {
        $role = Role::where('name', 'admin')->first();

        if (! $role) {
            return;
        }

        $permissions = [
            ['name' => "$model view", 'guard_name' => 'web'],
            ['name' => "$model create", 'guard_name' => 'web'],
            ['name' => "$model edit", 'guard_name' => 'web'],
            ['name' => "$model delete", 'guard_name' => 'web'],
        ];

        foreach ($permissions as $p) {
            $permission = Permission::firstOrCreate($p);

            $role->givePermissionTo(permissions: $permission);
        }
    }
}
