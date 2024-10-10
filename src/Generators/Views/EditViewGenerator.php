<?php

namespace EvdigiIna\Generator\Generators\Views;

use EvdigiIna\Generator\Generators\GeneratorUtils;

class EditViewGenerator
{
    /**
     * Generate an edit view.
     */
    public function generate(array $request): void
    {
        $model = GeneratorUtils::setModelName(model: $request['model'], style: 'default');
        $path = GeneratorUtils::getModelLocation(model: $request['model']);

        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase(string: $model);
        $modelNameSingularLowerCase = GeneratorUtils::cleanSingularLowerCase(string: $model);
        $modelNameSingularCamelCase = GeneratorUtils::singularCamelCase(string: $model);

        $template = GeneratorUtils::replaceStub(
            replaces: [
                'modelNamePluralUcWords' => GeneratorUtils::cleanPluralUcWords(string: $model),
                'modelNameSingularLowerCase' => $modelNameSingularLowerCase,
                'modelNamePluralKebabCase' => $modelNamePluralKebabCase,
                'modelNameSingularCamelCase' => $modelNameSingularCamelCase,
                'enctype' => in_array(needle: 'file', haystack: $request['input_types']) ? ' enctype="multipart/form-data"' : '',
                'viewPath' => $path != '' ? str_replace(search: '\\', replace: '.', subject: strtolower(string: $path)) . "." : '',
            ],
            stubName: empty($request['is_simple_generator']) ? 'views/edit' : 'views/simple/edit'
        );

        if (!$path) {
            GeneratorUtils::checkFolder(path: resource_path(path: "/views/$modelNamePluralKebabCase"));
            file_put_contents(filename: resource_path(path: "/views/$modelNamePluralKebabCase/edit.blade.php"), data: $template);
        } else {
            $fullPath = resource_path(path: "/views/" . strtolower(string: $path) . "/$modelNamePluralKebabCase");
            GeneratorUtils::checkFolder(path: $fullPath);
            file_put_contents(filename: $fullPath . "/edit.blade.php", data: $template);
        }
    }
}
