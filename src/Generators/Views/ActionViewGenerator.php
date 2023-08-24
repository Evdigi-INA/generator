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
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);

        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase($model);
        $modelNameSingularLowercase = GeneratorUtils::cleanSingularLowerCase($model);

        $template = str_replace(
            [
                '{{modelNameSingularLowercase}}',
                '{{modelNamePluralKebabCase}}'
            ],
            [
                $modelNameSingularLowercase,
                $modelNamePluralKebabCase
            ],
            empty($request['is_simple_generator']) ? GeneratorUtils::getTemplate('views/action') : GeneratorUtils::getTemplate('views/simple/action')
        );

        if ($path != '') {
            $fullPath = resource_path("/views/" . strtolower($path) . "/$modelNamePluralKebabCase/include");

            GeneratorUtils::checkFolder($fullPath);

            file_put_contents($fullPath . "/action.blade.php", $template);
        } else {
            GeneratorUtils::checkFolder(resource_path("/views/$modelNamePluralKebabCase/include"));

            file_put_contents(resource_path("/views/$modelNamePluralKebabCase/include/action.blade.php"), $template);
        };
    }
}
