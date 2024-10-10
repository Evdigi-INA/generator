<?php

namespace EvdigiIna\Generator\Generators\Views;

use EvdigiIna\Generator\Generators\GeneratorUtils;

class ShowViewGenerator
{
    /**
     * Generate a show view.
     */
    public function generate(array $request): void
    {
        $model = GeneratorUtils::setModelName(model: $request['model'], style: 'default');
        $path = GeneratorUtils::getModelLocation(model: $request['model']);

        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase(string: $model);
        $modelNameSingularCamelCase = GeneratorUtils::singularCamelCase(string: $model);

        $trs = $this->generateTableRows(request: $request, model: $model, modelName: $modelNameSingularCamelCase);

        $template = GeneratorUtils::replaceStub(
            replaces: [
                'modelNamePluralUcWords' => GeneratorUtils::cleanPluralUcWords(string: $model),
                'modelNameSingularLowerCase' => GeneratorUtils::cleanSingularLowerCase(string: $model),
                'modelNamePluralKebabCase' => $modelNamePluralKebabCase,
                'modelNameSingularCamelCase' => $modelNameSingularCamelCase,
                'trs' => $trs,
                'dateTimeFormat' => config(key: 'generator.format.datetime', default: 'Y-m-d H:i:s'),
            ],
            stubName: empty($request['is_simple_generator']) ? 'views/show' : 'views/simple/show'
        );

        $this->saveTemplate(template: $template, path: $path, modelName: $modelNamePluralKebabCase);
    }

    /**
     * Generate table rows for the view.
     */
    private function generateTableRows(array $request, string $model, string $modelName): string
    {
        $trs = "";
        $totalFields = count(value: $request['fields']);
        $dateTimeFormat = config(key: 'generator.format.datetime', default: 'Y-m-d H:i:s');

        foreach ($request['fields'] as $i => $field) {
            if ($request['input_types'][$i] !== 'password') {
                $trs .= $this->generateTableRow(request: $request, i: $i, field: $field, model: $model, modelName: $modelName, dateTimeFormat: $dateTimeFormat);
                if ($i + 1 !== $totalFields) {
                    $trs .= "\n";
                }
            }
        }

        return $trs;
    }

    /**
     * Generate a single table row.
     */
    private function generateTableRow(array $request, int $i, string $field, string $model, string $modelName, string $dateTimeFormat): string
    {
        $fieldUcWords = GeneratorUtils::cleanUcWords(string: $field);
        $fieldSnakeCase = str(string: $field)->snake();
        $trs = "";

        if (isset($request['file_types'][$i]) && $request['file_types'][$i] === 'image') {
            $trs .= $this->generateImageRow(request: $request, i: $i, field: $field, model: $model, fieldUcWords: $fieldUcWords);
        } else {
            $trs .= match ($request['column_types'][$i]) {
                'boolean' => "<tr>
                                <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                                <td>{{ $" . $modelName . "->$fieldSnakeCase == 1 ? 'True' : 'False' }}</td>
                              </tr>",
                'foreignId' => $this->generateForeignIdRow(request: $request, i: $i, modelName: $modelName),
                'date' => $this->generateDateRow(request: $request, i: $i, modelName: $modelName, fieldUcWords: $fieldUcWords, fieldSnakeCase: $fieldSnakeCase),
                'dateTime' => "<tr>
                                <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                                <td>{{ isset($" . $modelName . "->$fieldSnakeCase) ? $" . $modelName . "->" . $fieldSnakeCase . "?->format(\"$dateTimeFormat\") : '' }}</td>
                               </tr>",
                'time' => $this->generateTimeRow(modelName: $modelName, fieldUcWords: $fieldUcWords, fieldSnakeCase: $fieldSnakeCase),
                default => $this->generateDefaultRow(request: $request, i: $i, modelName: $modelName, fieldUcWords: $fieldUcWords, fieldSnakeCase: $fieldSnakeCase),
            };
        }

        return $trs;
    }

    private function generateDefaultRow(array $request, int $i, string $modelName, string $fieldUcWords, string $fieldSnakeCase): string
    {
        if ($request['input_types'][$i] == 'week') {
            $weekFormat = config(key: 'generator.format.week', default: 'Y-\WW');

            return "<tr>
                    <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                    <td>{{ isset($" . $modelName . "->$fieldSnakeCase) ? $" . $modelName . "->" . $fieldSnakeCase . "?->format(\"$weekFormat\") : '' }}</td>
                </tr>";
        }

        return "<tr>
                    <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                    <td>{{ $" . $modelName . "->$fieldSnakeCase }}</td>
                </tr>";
    }

    /**
     * Generate a row for image fields.
     */
    private function generateImageRow(array $request, int $i, string $field, string $model, string $fieldUcWords): string
    {
        $default = GeneratorUtils::setDefaultImage(
            default: $request['default_values'][$i],
            field: $request['fields'][$i],
            model: $model
        );

        $castImage = str_replace(
            search: "\$this->" . GeneratorUtils::singularCamelCase(string: $field) . "Path",
            replace: "'" . GeneratorUtils::pluralKebabCase(string: $field) . "/'",
            subject: GeneratorUtils::setDiskCodeForCastImage(model: $model, field: $field)
        );

        return "<tr>
                    <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                    <td>
                        @if (" . $default['form_code'] . ")
                            <img src=\"" . $default['image'] . "\" alt=\"$fieldUcWords\" class=\"rounded img-fluid\">
                        @else
                            <img src=\"{{ " . $castImage . " }}\" alt=\"$fieldUcWords\" class=\"rounded img-fluid\">
                        @endif
                    </td>
                </tr>";
    }

    /**
     * Generate a row for foreign ID fields.
     */
    private function generateForeignIdRow(array $request, int $i, string $modelName): string
    {
        $constrainModel = GeneratorUtils::setModelName(model: $request['constrains'][$i], style: 'default');

        return "<tr>
                    <td class=\"fw-bold\">{{ __('" . GeneratorUtils::cleanSingularUcWords(string: $constrainModel) . "') }}</td>
                    <td>{{ $" . $modelName . "->" . GeneratorUtils::singularSnakeCase(string: $constrainModel) . " ? $" . $modelName . "->" . GeneratorUtils::singularSnakeCase(string: $constrainModel) . "->" . GeneratorUtils::getColumnAfterId($constrainModel) . " : '' }}</td>
                </tr>";
    }

    /**
     * Generate a row for date fields.
     */
    private function generateDateRow(array $request, int $i, string $modelName, string $fieldUcWords, string $fieldSnakeCase): string
    {
        $dateFormat = match ($request['input_types'][$i]) {
            'month' => config(key: 'generator.format.month', default: 'Y/m'),
            default => config(key: 'generator.format.date', default: 'd/m/Y'),
        };

        return "<tr>
                    <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                    <td>{{ isset($" . $modelName . "->$fieldSnakeCase) ? $" . $modelName . "->" . $fieldSnakeCase . "?->format(\"$dateFormat\") : '' }}</td>
                </tr>";
    }

    /**
     * Generate a row for time fields.
     */
    private function generateTimeRow(string $modelName, string $fieldUcWords, string $fieldSnakeCase): string
    {
        $timeFormat = config(key: 'generator.format.time', default: 'H:i');

        return "<tr>
                    <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                    <td>{{ isset($" . $modelName . "->$fieldSnakeCase) ? $" . $modelName . "->" . $fieldSnakeCase . "?->format(\"$timeFormat\") : '' }}</td>
                </tr>";
    }

    /**
     * Save the generated template to the specified path.
     */
    private function saveTemplate(string $template, string $path, string $modelName): void
    {
        $viewPath = $path ? resource_path(path: "/views/" . strtolower(string: $path) . "/$modelName") : resource_path(path: "/views/$modelName");
        GeneratorUtils::checkFolder(path: $viewPath);
        file_put_contents(filename: "$viewPath/show.blade.php", data: $template);
    }
}
