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

        if (GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value) {
            if (empty($request['is_simple_generator'])) {
                $alertCode = '<x-alert></x-alert>';
            } else {
                $alertCode = "@if (session('success'))
            <div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                    <h4 class=\"alert-heading\">{{ __('Success') }}</h4>
                    <p>{{ session('success') }}</p>
            </div>
        @endif";
            }
        } else {
            $alertCode = '';
        }

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
                $alertCode
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
}
