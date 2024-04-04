<?php

namespace EvdigiIna\Generator\Generators\Views;

use EvdigiIna\Generator\Enums\GeneratorVariant;
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
            empty($request['is_simple_generator']) ? GeneratorUtils::getStub('views/create') : GeneratorUtils::getStub('views/simple/create')
        );

        // add alert to create page in single form crud
        /*
         * will generate something like:
         *  <section class="section">
         *      <x-alert></x-alert>
         */
        if (GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value) {
            $template = str_replace('<section class="section">', "<section class=\"section\">\n\t\t\t<x-alert></x-alert>\n", $template);
        }

        if ($path) {
            $fullPath = resource_path("/views/" . strtolower($path) . "/$modelNamePluralKebabCase");

            GeneratorUtils::checkFolder($fullPath);

            file_put_contents($fullPath . "/create.blade.php", $template);
        } else {
            GeneratorUtils::checkFolder(resource_path("/views/$modelNamePluralKebabCase"));

            file_put_contents(resource_path("/views/$modelNamePluralKebabCase/create.blade.php"), $template);
        }
    }
}
