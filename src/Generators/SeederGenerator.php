<?php

namespace EvdigiIna\Generator\Generators;

use Illuminate\Support\Facades\Log;

class SeederGenerator
{
    public function generate(array $request): void
    {
        if (isset($request['generate_seeder'])) {
            $model = GeneratorUtils::setModelName($request['model'], 'default');
            $path = GeneratorUtils::getModelLocation($request['model']);
            $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase($model);

            $fields = "";
            $totalField = count($request['fields']);
            foreach ($request['fields'] as $i => $field) {
                $fields .= "'" . $field . "' => ";
                $fields .= $request['requireds'][$i] == 'yes' ? "''" : "null";

                if ($i + 1 != $totalField)
                    $fields .= ",\r\n\t\t\t";
            }

            $modelPath = $path ? $path . "\\" : "";

            $template = GeneratorUtils::replaceStub(
                replaces: [
                    'modelPath' => "App\Models\\" . $modelPath . $modelNameSingularPascalCase,
                    'fields' => $fields,
                    'modelNameSingularPascalCase' => $modelNameSingularPascalCase,
                    'modelNameSingularCamelCase' => GeneratorUtils::singularCamelCase($model),
                    'modelNamePluralCamelCase' => GeneratorUtils::pluralCamelCase($model)
                ],
                stubName: 'seeder'
            );

            file_put_contents(app_path("../database/seeders/" . $modelNameSingularPascalCase . "Seeder.php"), $template);
        }
    }
}
