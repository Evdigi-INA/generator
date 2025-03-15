<?php

namespace EvdigiIna\Generator\Generators;

class SeederGenerator
{
    public function generate(array $request): void
    {
        if (isset($request['generate_seeder'])) {
            $model = GeneratorUtils::setModelName(model: $request['model'], style: 'default');
            $path = GeneratorUtils::getModelLocation(model: $request['model']);
            $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase(string: $model);

            $fields = '';
            $totalField = count(value: $request['fields']);
            foreach ($request['fields'] as $i => $field) {
                $fields .= "'".$field."' => ";
                $fields .= $request['requireds'][$i] == 'yes' ? "''" : 'null';

                if ($i + 1 != $totalField) {
                    $fields .= ",\r\n\t\t\t";
                }
            }

            $modelPath = $path ? $path.'\\' : '';

            $template = GeneratorUtils::replaceStub(
                replaces: [
                    'modelPath' => "App\Models\\".$modelPath.$modelNameSingularPascalCase,
                    'fields' => $fields,
                    'modelNameSingularPascalCase' => $modelNameSingularPascalCase,
                    'modelNameSingularCamelCase' => GeneratorUtils::singularCamelCase(string: $model),
                    'modelNamePluralCamelCase' => GeneratorUtils::pluralCamelCase(string: $model),
                ],
                stubName: 'seeder'
            );

            file_put_contents(filename: app_path(path: '../database/seeders/'.$modelNameSingularPascalCase.'Seeder.php'), data: $template);
        }
    }
}
