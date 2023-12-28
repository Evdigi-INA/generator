<?php

namespace EvdigiIna\Generator\Generators;

class FactoryGenerator
{
    public function generate(array $request)
    {
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);
        $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase($model);

        $fields = "";
        $totalField = count($request['fields']);
        foreach ($request['fields'] as $i => $field) {
            $fields .= "// '" . $field ."' => ";
            $fields .= $request['requireds'][$i] == 'yes' ? "\$this->faker->" : "null";

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
            ],
            [
                "App\Models\\" . $modelPath . $modelNameSingularPascalCase,
                $fields,
                $modelNameSingularPascalCase,
            ],
            GeneratorUtils::getStub('factory')
        );

        file_put_contents(app_path("../database/factories/" . $modelNameSingularPascalCase . "Factory.php"), $template);
    }
}
