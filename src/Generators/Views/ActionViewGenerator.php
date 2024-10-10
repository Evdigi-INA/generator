<?php

namespace EvdigiIna\Generator\Generators\Views;

use EvdigiIna\Generator\Generators\GeneratorUtils;

class ActionViewGenerator
{
    /**
     * Generate a action(table) view.
     */
    public function generate(array $request): void
    {
        $model = GeneratorUtils::setModelName(model: $request['model'], style: 'default');
        $path = GeneratorUtils::getModelLocation(model: $request['model']);

        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase(string: $model);
        $modelNameSingularLowercase = GeneratorUtils::cleanSingularLowerCase(string: $model);

        $template = GeneratorUtils::replaceStub(
            replaces: [
                'modelNameSingularLowercase' => $modelNameSingularLowercase,
                'modelNamePluralKebabCase' => $modelNamePluralKebabCase,
            ],
            stubName: empty($request['is_simple_generator']) ? 'views/action' : 'views/simple/action'
        );

        if ($path != '') {
            $fullPath = resource_path(path: "/views/" . strtolower(string: $path) . "/$modelNamePluralKebabCase/include");

            GeneratorUtils::checkFolder(path: $fullPath);

            file_put_contents(filename: $fullPath . "/action.blade.php", data: $template);
        } else {
            GeneratorUtils::checkFolder(path: resource_path(path: "/views/$modelNamePluralKebabCase/include"));

            file_put_contents(filename: resource_path(path: "/views/$modelNamePluralKebabCase/include/action.blade.php"), data: $template);
        }
    }
}
