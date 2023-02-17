<?php

namespace EvdigiIna\Generator\Generators;

use Spatie\Permission\Models\{Role, Permission};

class PermissionGenerator
{
    /**
     * Generate new permissions to confg.permissions.permissions(used for peermissios seeder).
     *
     * @param array $request
     * @return void
     */
    public function generate(array $request)
    {
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $modelNamePlural = GeneratorUtils::cleanPluralLowerCase($model);
        $modelNameSingular = GeneratorUtils::cleanSingularLowerCase($model);

        $stringPermissions = str_replace(
            [
                '{',
                '}',
                ':',
                '"',
                ',',
                ']]'
            ],
            [
                '[',
                ']',
                " => ",
                "'",
                ', ',
                "]], \n\t\t"
            ],
            json_encode([
                'group' => $modelNamePlural,
                'access' => [
                    "$modelNameSingular view",
                    "$modelNameSingular create",
                    "$modelNameSingular edit",
                    "$modelNameSingular delete",
                ]
            ])
        );

        $path = config_path('permission.php');

        $newPermissionFile = substr(file_get_contents($path), 0, -8) .  $stringPermissions . "],];";

        file_put_contents($path, $newPermissionFile);

        $this->insertRoleAndPermissions($modelNameSingular);
    }

    /**
     * Insert new role & permissions then give an admin that permissions.
     *
     * @param array $request
     * @return void
     */
    protected function insertRoleAndPermissions(string $model)
    {
        $role = Role::findByName('admin');

        Permission::firstOrCreate(['name' => "$model view"]);
        Permission::firstOrCreate(['name' => "$model create"]);
        Permission::firstOrCreate(['name' => "$model edit"]);
        Permission::firstOrCreate(['name' => "$model delete"]);

        $role->givePermissionTo([
            "$model view",
            "$model create",
            "$model edit",
            "$model delete"
        ]);
    }
}
