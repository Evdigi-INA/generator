<?php

namespace EvdigiIna\Generator\Generators;

class FactoryGenerator
{
    public function generate(array $request): void
    {
        if (isset($request['generate_factory'])) {
            $model = GeneratorUtils::setModelName(model: $request['model'], style: 'default');
            $path = GeneratorUtils::getModelLocation(model: $request['model']);
            $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase(string: $model);

            $fields = '';
            $totalField = count(value: $request['fields']);

            foreach ($request['fields'] as $i => $field) {
                $fields .= "// '".$field."' => ";
                $fields .= $request['requireds'][$i] == 'yes' ? '$this->faker->' : 'null';

                if ($i + 1 != $totalField) {
                    $fields .= ",\r\n\t\t\t";
                }
            }

            $modelPath = $path ? "$path\\" : '';

            $replaces = [
                'modelPath' => "App\Models\\$modelPath".$modelNameSingularPascalCase,
                'fields' => $fields,
                'modelNameSingularPascalCase' => $modelNameSingularPascalCase,
            ];

            $template = GeneratorUtils::replaceStub(replaces: $replaces, stubName: 'factory');

            file_put_contents(filename: app_path(path: '../database/factories/'.$modelNameSingularPascalCase.'Factory.php'), data: $template);
        }
    }
}
