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

        $template = GeneratorUtils::replaceStub(
            replaces: [
                'modelNamePluralUcWords' => $modelNamePluralUcWords,
                'modelNameSingularLowerCase' => $modelNameSingularLowerCase,
                'modelNamePluralKebabCase' => $modelNamePluralKebabCase,
                'enctype' => in_array(needle: 'file', haystack: $request['input_types']) ? ' enctype="multipart/form-data"' : '',
                'viewPath' => $path != '' ? str_replace(search: '\\', replace: '.', subject: strtolower(string: $path)).'.' : '',
                'alertForSingleForm' => $alertCode,
                'exportButton' => $request['generate_variant'] == GeneratorVariant::SINGLE_FORM->value
                    ? (new IndexViewGenerator)->generateExportButton(request: $request) : '',
            ],
            stubName: empty($request['is_simple_generator']) ? 'views/create' : 'views/simple/create'
        );

        if ($path) {
            $fullPath = resource_path(path: '/views/'.strtolower(string: $path)."/$modelNamePluralKebabCase");

            GeneratorUtils::checkFolder(path: $fullPath);

            file_put_contents(filename: $fullPath.'/create.blade.php', data: $template);
        } else {
            GeneratorUtils::checkFolder(path: resource_path(path: "/views/$modelNamePluralKebabCase"));

            file_put_contents(filename: resource_path(path: "/views/$modelNamePluralKebabCase/create.blade.php"), data: $template);
        }
    }
}
