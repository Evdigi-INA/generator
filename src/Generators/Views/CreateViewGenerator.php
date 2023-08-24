<?php

namespace EvdigiIna\Generator\Generators\Views;

use EvdigiIna\Generator\Generators\GeneratorUtils;

class CreateViewGenerator
{
    /**
     * Generate a create view.
     */
    public function generate(array $request): void
    {
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);

        $modelNamePluralUcWords = GeneratorUtils::cleanPluralUcWords($model);
        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase($model);
        $modelNameSingularLowerCase = GeneratorUtils::cleanSingularLowerCase($model);

        $template = str_replace(
            [
                '{{modelNamePluralUcWords}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralKebabCase}}',
                '{{enctype}}',
                '{{viewPath}}',
            ],
            [
                $modelNamePluralUcWords,
                $modelNameSingularLowerCase,
                $modelNamePluralKebabCase,
                in_array('file', $request['input_types']) ? ' enctype="multipart/form-data"' : '',
                $path != '' ? str_replace('\\', '.', strtolower($path)) . "." : '',
            ],
            empty($request['is_simple_generator']) ? GeneratorUtils::getTemplate('views/create') : GeneratorUtils::getTemplate('views/simple/create')
        );


        if (!$path) {
            GeneratorUtils::checkFolder(resource_path("/views/$modelNamePluralKebabCase"));
            file_put_contents(resource_path("/views/$modelNamePluralKebabCase/create.blade.php"), $template);
        } else {
            $fullPath = resource_path("/views/" . strtolower($path) . "/$modelNamePluralKebabCase");
            GeneratorUtils::checkFolder($fullPath);
            file_put_contents($fullPath . "/create.blade.php", $template);
        }
    }
}
