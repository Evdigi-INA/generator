<?php

namespace EvdigiIna\Generator\Generators;

use Illuminate\Support\Facades\Log;

class SeederGenerator
{
    public function generate(array $request)
    {
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);
        $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase($model);

        $fields = "";
        $totalField = count($request['fields']);
        foreach ($request['fields'] as $i => $field) {
            $fields .= "'" . $field ."' => ";
            $fields .= $request['requireds'][$i] == 'yes' ? "''" : "null";

            if($i + 1 != $totalField) {
                $fields .= ",\r\n\t\t\t";
            }
        }

        $modelPath = $path ? $path . "\\" : "";

        $template = str_replace(
            [
                '{{modelPath}}',
                '{{fields}}',
                '{{modelNameSingularPascalCase}}',
                '{{modelNameSingularCamelCase}}',
                '{{modelNamePluralCamelCase}}'
            ],
            [
                "App\Models\\" . $modelPath . $modelNameSingularPascalCase,
                $fields,
                $modelNameSingularPascalCase,
                GeneratorUtils::singularCamelCase($model),
                GeneratorUtils::pluralCamelCase($model)
            ],
            GeneratorUtils::getTemplate('seeder')
        );

        file_put_contents(app_path("../database/seeders/" . $modelNameSingularPascalCase . "Seeder.php"), $template);
    }
}
