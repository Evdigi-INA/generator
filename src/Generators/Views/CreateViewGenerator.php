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
                '{{alertForSingleForm}}'
            ],
            [
                $modelNamePluralUcWords,
                $modelNameSingularLowerCase,
                $modelNamePluralKebabCase,
                in_array('file', $request['input_types']) ? ' enctype="multipart/form-data"' : '',
                $path != '' ? str_replace('\\', '.', strtolower($path)) . "." : '',
                GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value ? '<x-alert></x-alert>' : ''
            ],
            GeneratorUtils::getStub(empty($request['is_simple_generator']) ? 'views/create' : 'views/simple/create')
        );

        if ($path) {
            $fullPath = resource_path("/views/" . strtolower($path) . "/$modelNamePluralKebabCase");

            GeneratorUtils::checkFolder($fullPath);

            file_put_contents($fullPath . "/create.blade.php", $template);
        } else {
            GeneratorUtils::checkFolder(resource_path("/views/$modelNamePluralKebabCase"));

            file_put_contents(resource_path("/views/$modelNamePluralKebabCase/create.blade.php"), $template);
        }
    }

    public function setCreateViewStub(): string
    {
        // if(GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value && isset($request['is_simple_generator'])){
        //     return 'views/simple/create';
        // }

        // return empty($request['is_simple_generator']) ? 'views/create' : 'views/simple/create';

        switch(GeneratorUtils::checkGeneratorVariant()){
            case GeneratorVariant::SINGLE_FORM->value:
                if(empty($request['is_simple_generator'])){
                    return 'views/create';
                }

                return 'views/simple/create';
            default:
                return 'views/create';
        }
    }
}
