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
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);

        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase($model);
        $modelNameSingularCamelCase = GeneratorUtils::singularCamelCase($model);

        $trs = $this->generateTableRows($request, $model, $modelNameSingularCamelCase);

        $template = str_replace(
            [
                '{{modelNamePluralUcWords}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralKebabCase}}',
                '{{modelNameSingularCamelCase}}',
                '{{trs}}',
                '{{dateTimeFormat}}',
            ],
            [
                GeneratorUtils::cleanPluralUcWords($model),
                GeneratorUtils::cleanSingularLowerCase($model),
                $modelNamePluralKebabCase,
                $modelNameSingularCamelCase,
                $trs,
                config('generator.format.datetime', 'Y-m-d H:i:s'),
            ],
            empty($request['is_simple_generator']) ? GeneratorUtils::getStub('views/show') : GeneratorUtils::getStub('views/simple/show')
        );

        $this->saveTemplate($template, $path, $modelNamePluralKebabCase);
    }

    /**
     * Generate table rows for the view.
     */
    private function generateTableRows(array $request, string $model, string $modelNameSingularCamelCase): string
    {
        $trs = "";
        $totalFields = count($request['fields']);
        $dateTimeFormat = config('generator.format.datetime', 'Y-m-d H:i:s');

        foreach ($request['fields'] as $i => $field) {
            if ($request['input_types'][$i] !== 'password') {
                $trs .= $this->generateTableRow($request, $i, $field, $model, $modelNameSingularCamelCase, $dateTimeFormat);
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
    private function generateTableRow(array $request, int $i, string $field, string $model, string $modelNameSingularCamelCase, string $dateTimeFormat): string
    {
        $fieldUcWords = GeneratorUtils::cleanUcWords($field);
        $fieldSnakeCase = str($field)->snake();
        $trs = "";

        if (isset($request['file_types'][$i]) && $request['file_types'][$i] === 'image') {
            $trs .= $this->generateImageRow($request, $i, $field, $model, $fieldUcWords);
        } else {
            $trs .= match ($request['column_types'][$i]) {
                'boolean' => "<tr>
                                <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                                <td>{{ $" . $modelNameSingularCamelCase . "->$fieldSnakeCase == 1 ? 'True' : 'False' }}</td>
                              </tr>",
                'foreignId' => $this->generateForeignIdRow($request, $i, $modelNameSingularCamelCase),
                'date' => $this->generateDateRow($request, $i, $modelNameSingularCamelCase, $fieldUcWords, $fieldSnakeCase),
                'dateTime' => "<tr>
                                <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                                <td>{{ isset($" . $modelNameSingularCamelCase . "->$fieldSnakeCase) ? $" . $modelNameSingularCamelCase . "->" . $fieldSnakeCase . "?->format(\"$dateTimeFormat\") : '' }}</td>
                               </tr>",
                'time' => $this->generateTimeRow($modelNameSingularCamelCase, $fieldUcWords, $fieldSnakeCase),
                default => $this->generateDefaultRow($request, $i, $modelNameSingularCamelCase, $fieldUcWords, $fieldSnakeCase),
            };
        }

        return $trs;
    }

    private function generateDefaultRow(array $request, int $i, string $modelNameSingularCamelCase, string $fieldUcWords, string $fieldSnakeCase): string
    {
        if ($request['input_types'][$i] == 'week') {
            $weekFormat = config('generator.format.week', 'Y-\WW');

            return "<tr>
                    <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                    <td>{{ isset($" . $modelNameSingularCamelCase . "->$fieldSnakeCase) ? $" . $modelNameSingularCamelCase . "->" . $fieldSnakeCase . "?->format(\"$weekFormat\") : '' }}</td>
                </tr>";
        }

        return "<tr>
                    <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                    <td>{{ $" . $modelNameSingularCamelCase . "->$fieldSnakeCase }}</td>
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
            "\$this->" . GeneratorUtils::singularCamelCase($field) . "Path",
            "'" . GeneratorUtils::pluralKebabCase($field) . "/'",
            GeneratorUtils::setDiskCodeForCastImage($model, $field)
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
    private function generateForeignIdRow(array $request, int $i, string $modelNameSingularCamelCase): string
    {
        $constrainModel = GeneratorUtils::setModelName($request['constrains'][$i], 'default');

        return "<tr>
                    <td class=\"fw-bold\">{{ __('" . GeneratorUtils::cleanSingularUcWords($constrainModel) . "') }}</td>
                    <td>{{ $" . $modelNameSingularCamelCase . "->" . GeneratorUtils::singularSnakeCase($constrainModel) . " ? $" . $modelNameSingularCamelCase . "->" . GeneratorUtils::singularSnakeCase($constrainModel) . "->" . GeneratorUtils::getColumnAfterId($constrainModel) . " : '' }}</td>
                </tr>";
    }

    /**
     * Generate a row for date fields.
     */
    private function generateDateRow(array $request, int $i, string $modelNameSingularCamelCase, string $fieldUcWords, string $fieldSnakeCase): string
    {
        $dateFormat = match ($request['input_types'][$i]) {
            'month' => config('generator.format.month', 'Y/m'),
            default => config('generator.format.date', 'd/m/Y'),
        };

        return "<tr>
                    <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                    <td>{{ isset($" . $modelNameSingularCamelCase . "->$fieldSnakeCase) ? $" . $modelNameSingularCamelCase . "->" . $fieldSnakeCase . "?->format(\"$dateFormat\") : '' }}</td>
                </tr>";
    }

    /**
     * Generate a row for time fields.
     */
    private function generateTimeRow(string $modelNameSingularCamelCase, string $fieldUcWords, string $fieldSnakeCase): string
    {
        $timeFormat = config('generator.format.time', 'H:i');

        return "<tr>
                    <td class=\"fw-bold\">{{ __('$fieldUcWords') }}</td>
                    <td>{{ isset($" . $modelNameSingularCamelCase . "->$fieldSnakeCase) ? $" . $modelNameSingularCamelCase . "->" . $fieldSnakeCase . "?->format(\"$timeFormat\") : '' }}</td>
                </tr>";
    }

    /**
     * Save the generated template to the specified path.
     */
    private function saveTemplate(string $template, string $path, string $modelNamePluralKebabCase): void
    {
        $viewPath = $path ? resource_path("/views/" . strtolower($path) . "/$modelNamePluralKebabCase") : resource_path("/views/$modelNamePluralKebabCase");
        GeneratorUtils::checkFolder($viewPath);
        file_put_contents("$viewPath/show.blade.php", $template);
    }
}
