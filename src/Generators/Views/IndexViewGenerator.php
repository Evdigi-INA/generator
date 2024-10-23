<?php

namespace EvdigiIna\Generator\Generators\Views;

use EvdigiIna\Generator\Generators\GeneratorUtils;

class IndexViewGenerator
{
    /**
     * Generate an index view.
     */
    public function generate(array $request): void
    {
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);

        $modelNamePluralUcWords = GeneratorUtils::cleanPluralUcWords($model);
        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase($model);
        $modelNamePluralLowerCase = GeneratorUtils::cleanPluralLowerCase($model);
        $modelNameSingularLowercase = GeneratorUtils::cleanSingularLowerCase($model);

        $thColumns = '';
        $tdColumns = '';
        $totalFields = count($request['fields']);

        foreach ($request['fields'] as $i => $field) {
            if ($request['input_types'][$i] != 'password') {
                /**
                 * will generate something like:
                 * <th>{{ __('Price') }}</th>
                 */
                if ($request['column_types'][$i] != 'foreignId') {
                    $thColumns .= "<th>{{ __('" . GeneratorUtils::cleanUcWords($field) . "') }}</th>";
                }

                if ($request['input_types'][$i] == 'file') {
                    /**
                     * will generate something like:
                     * {
                     *    data: 'photo',
                     *    name: 'photo',
                     *    orderable: false,
                     *    searchable: false,
                     *    render: function(data) {
                     *        return `<div class="avatar">
                     *            <img src="${data}" alt="Photo"/>
                     *        </div>`;
                     *    }
                     * },
                     */

                    $imgStyle = '';
                    if (isset($request['is_simple_generator'])) {
                        $imgStyle = 'class="rounded" width="50" height="40" style="object-fit: cover"';
                    }

                    $tdColumns .= "{
                    data: '" . str()->snake($field) . "',
                    name: '" . str()->snake($field) . "',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        return `<div class=\"avatar\">
                            <img src=\"" . '$' . "{data}\" alt=\"" . GeneratorUtils::cleanSingularUcWords($field) . "\" $imgStyle>
                        </div>`;
                        }
                    },";
                } elseif ($request['column_types'][$i] == 'foreignId') {
                    // remove '/' or sub folders
                    $constrainModel = GeneratorUtils::setModelName($request['constrains'][$i], 'default');

                    $thColumns .= "<th>{{ __('" . GeneratorUtils::cleanSingularUcWords($constrainModel) . "') }}</th>";

                    /**
                     * will generate something like:
                     * {
                     *    data: 'user',
                     *    name: 'user.name'
                     * }
                     */
                    $tdColumns .= "{
                    data: '" . GeneratorUtils::singularSnakeCase($constrainModel) . "',
                    name: '" . GeneratorUtils::singularSnakeCase($constrainModel) . "." . GeneratorUtils::getColumnAfterId($constrainModel) . "'
                },";
                } else {
                    /**
                     * will generate something like:
                     * {
                     *    data: 'price',
                     *    name: 'price'
                     * }
                     */
                    $tdColumns .= "{
                    data: '" . str()->snake($field) . "',
                    name: '" . str()->snake($field) . "',
                },";
                }

                if ($i + 1 != $totalFields) {
                    // add new line and tab
                    $thColumns .= "\n\t\t\t\t\t\t\t\t\t\t\t";
                    $tdColumns .= "\n\t\t\t\t";
                }
            }
        }

        $template = GeneratorUtils::replaceStub(
            replaces: [
                'modelNamePluralUcWords' => $modelNamePluralUcWords,
                'modelNamePluralKebabCase' => $modelNamePluralKebabCase,
                'modelNameSingularLowerCase' => $modelNameSingularLowercase,
                'modelNamePluralLowerCase' => $modelNamePluralLowerCase,
                'thColumns' => $thColumns,
                'tdColumns' => $tdColumns,
                'exportButton' => $this->generateExportButton(request: $request),
            ],
            stubName: empty($request['is_simple_generator']) ? 'views/index' : 'views/simple/index'
        );

        switch ($path) {
            case '':
                GeneratorUtils::checkFolder(resource_path("/views/$modelNamePluralKebabCase"));
                file_put_contents(resource_path("/views/$modelNamePluralKebabCase/index.blade.php"), $template);
                break;
            default:
                $fullPath = resource_path("/views/" . strtolower($path) . "/$modelNamePluralKebabCase");
                GeneratorUtils::checkFolder($fullPath);
                file_put_contents("$fullPath/index.blade.php", $template);
                break;
        }
    }

    public function generateExportButton(array $request): string
    {
        if (isset($request['generate_export']) && $request['generate_export'] == 'on') {
            return GeneratorUtils::replaceStub(
                replaces: [
                    'modelNamePluralKebabCase' => GeneratorUtils::pluralKebabCase($request['model']),
                    'modelNameSingularClean' => GeneratorUtils::cleanSingularLowerCase($request['model']),
                ],
                stubName: 'views/export-button'
            );
        }

        return '';
    }
}
