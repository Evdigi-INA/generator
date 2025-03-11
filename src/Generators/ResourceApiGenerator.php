<?php

namespace EvdigiIna\Generator\Generators;

class ResourceApiGenerator
{
    /**
     * Generate a controller file.
     */
    public function generate(array $request): void
    {
        if (GeneratorUtils::isGenerateApi()) {
            $model = GeneratorUtils::setModelName(model: $request['model'], style: 'default');
            $path = GeneratorUtils::getModelLocation(model: $request['model']);
            $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase(string: $model);
            $modelNamePluralPascalCase = GeneratorUtils::pluralPascalCase(string: $model);
            $namespace = ! $path ? "namespace App\Http\Resources\\$modelNamePluralPascalCase;" : "namespace App\Http\Resources\\$path\\$modelNamePluralPascalCase;";

            $resourceTemplate = GeneratorUtils::replaceStub(replaces: [
                'namespace' => $namespace,
                'modelNameSingularPascalCase' => $modelNameSingularPascalCase,
                'modelNameSingularSnakeCase' => GeneratorUtils::singularSnakeCase(string: $model),
            ], stubName: 'resource');

            $collectionTemplate = GeneratorUtils::replaceStub(replaces: [
                'namespace' => $namespace,
                'modelNameSingularPascalCase' => $modelNameSingularPascalCase,
                'modelNamePluralSnakeCase' => GeneratorUtils::pluralSnakeCase(string: $model),
            ], stubName: 'resource-collection');

            GeneratorUtils::checkFolder(path: app_path(path: "/Http/Resources/$modelNamePluralPascalCase"));

            if (! $path) {
                file_put_contents(filename: app_path(path: "/Http/Resources/$modelNamePluralPascalCase/".$modelNameSingularPascalCase.'Resource.php'), data: $resourceTemplate);

                file_put_contents(filename: app_path(path: "/Http/Resources/$modelNamePluralPascalCase/".$modelNameSingularPascalCase.'Collection.php'), data: $collectionTemplate);
            } else {
                $fullPath = app_path(path: "/Http/Resources/$path/$modelNamePluralPascalCase");

                GeneratorUtils::checkFolder(path: $fullPath);

                file_put_contents(filename: $fullPath.'/'.$modelNameSingularPascalCase.'Resource.php', data: $resourceTemplate);

                file_put_contents(filename: $fullPath.'/'.$modelNameSingularPascalCase.'Collection.php', data: $collectionTemplate);
            }
        }
    }
}
