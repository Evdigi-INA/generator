<?php

namespace EvdigiIna\Generator\Generators;

class ExportExcelGenerator
{
    public function generate(array $request): void
    {
        $stub = GeneratorUtils::getStub(path: 'export');
        $path = GeneratorUtils::getModelLocation($request['model']);
        $modelPath = $path ? $path . "\\" : "";
        $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase($request['model']);

        $template = str_replace(search: [
            '{{modelPath}}',
            '{{modelName}}'
        ], replace: [
            "App\Models\\" . $modelPath . $modelNameSingularPascalCase,
            $modelNameSingularPascalCase
        ], subject: $stub);

        GeneratorUtils::checkFolder(app_path(path: 'Exports'));

        file_put_contents(filename: app_path(path: "Exports/{$modelNameSingularPascalCase}Export.php"), data: $template);
    }
}
