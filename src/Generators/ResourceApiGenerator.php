<?php

namespace EvdigiIna\Generator\Generators;

class ResourceApiGenerator
{
    /**
     * Generate a controller file.
     */
    public function generate(array $request): void
    {
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);
        $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase($model);
        $modelNamePluralPascalCase = GeneratorUtils::pluralPascalCase($model);
        $namespace = !$path ? "namespace App\Http\Resources\\$modelNamePluralPascalCase;" : "namespace App\Http\Resources\\$path\\$modelNamePluralPascalCase;";

        $resourceTemplate = str_replace(
            [
                '{{namespace}}',
                '{{modelNameSingularPascalCase}}',
                '{{modelNameSingularSnakeCase}}'
            ],
            [
                $namespace,
                $modelNameSingularPascalCase,
                GeneratorUtils::singularSnakeCase($model)
            ],
            GeneratorUtils::getStub('resource')
        );

        $collectionTemplate = str_replace(
            [
                '{{namespace}}',
                '{{modelNameSingularPascalCase}}',
                '{{modelNamePluralSnakeCase}}'
            ],
            [
                $namespace,
                $modelNameSingularPascalCase,
                GeneratorUtils::pluralSnakeCase($model),
            ],
            GeneratorUtils::getStub('resource-collection')
        );

        GeneratorUtils::checkFolder(app_path("/Http/Resources/$modelNamePluralPascalCase"));

        if (!$path) {
            file_put_contents(app_path("/Http/Resources/$modelNamePluralPascalCase/" . $modelNameSingularPascalCase . "Resource.php"), $resourceTemplate);

            file_put_contents(app_path("/Http/Resources/$modelNamePluralPascalCase/" . $modelNameSingularPascalCase . "Collection.php"), $collectionTemplate);
        } else {
            $fullPath = app_path("/Http/Resources/$path/$modelNamePluralPascalCase");

            GeneratorUtils::checkFolder($fullPath);

            file_put_contents($fullPath . "/" . $modelNameSingularPascalCase . "Resource.php", $resourceTemplate);

            file_put_contents($fullPath . "/" . $modelNameSingularPascalCase . "Collection.php", $collectionTemplate);
        }
    }
}
