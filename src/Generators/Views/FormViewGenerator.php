<?php

namespace EvdigiIna\Generator\Generators\Views;

use EvdigiIna\Generator\Generators\GeneratorUtils;

class FormViewGenerator
{
    /**
     * Generate a form/input for create and edit.
     */
    public function generate(array $request): void
    {
        $model = GeneratorUtils::setModelName(model: $request['model'], style: 'default');
        $path = GeneratorUtils::getModelLocation(model: $request['model']);

        $modelNameSingularCamelCase = GeneratorUtils::singularCamelCase(string: $model);
        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase(string: $model);

        $template = "<div class=\"row mb-2\">\n";

        foreach ($request['fields'] as $i => $field) {

            if ($request['input_types'][$i] !== 'no-input') {
                $fieldSnakeCase = str(string: $field)->snake();
                $fieldUcWords = GeneratorUtils::cleanUcWords(string: $field);

                switch ($request['column_types'][$i]) {
                    case 'enum':
                        $options = '';

                        $arrOption = explode(separator: '|', string: $request['select_options'][$i]);

                        $totalOptions = count(value: $arrOption);

                        switch ($request['input_types'][$i]) {
                            case 'select':
                                // select
                                foreach ($arrOption as $arrOptionIndex => $value) {
                                    $options .= <<<BLADE
                                    <option value="$value" {{ isset(\$$modelNameSingularCamelCase) && \$$modelNameSingularCamelCase->$fieldSnakeCase == '$value' ? 'selected' : (old(key: '$fieldSnakeCase') == '$value' ? 'selected' : '') }}>$value</option>
                                    BLADE;

                                    if ($arrOptionIndex + 1 != $totalOptions) {
                                        $options .= "\n\t\t";
                                    } else {
                                        $options .= "\t\t\t";
                                    }
                                }

                                $template .= GeneratorUtils::replaceStub(
                                    replaces: [
                                        'fieldUcWords' => $fieldUcWords,
                                        'fieldKebabCase' => GeneratorUtils::kebabCase(string: $field),
                                        'fieldSnakeCase' => $fieldSnakeCase,
                                        'fieldSpaceLowercase' => GeneratorUtils::cleanLowerCase($field),
                                        'options' => $options,
                                        'nullable' => $request['requireds'][$i] == 'yes' ? ' required' : '',
                                    ],
                                    stubName: 'views/forms/select'
                                );
                                break;
                            case 'datalist':
                                foreach ($arrOption as $arrOptionIndex => $value) {
                                    $options .= '<option value="'.$value."\">$value</option>";

                                    if ($arrOptionIndex + 1 != $totalOptions) {
                                        $options .= "\n\t\t";
                                    } else {
                                        $options .= "\t\t\t";
                                    }
                                }

                                $template .= GeneratorUtils::replaceStub(
                                    replaces: [
                                        'fieldKebabCase' => GeneratorUtils::kebabCase($field),
                                        'fieldCamelCase' => GeneratorUtils::singularCamelCase($field),
                                        'fieldUcWords' => $fieldUcWords,
                                        'fieldSnakeCase' => $fieldSnakeCase,
                                        'options' => $options,
                                        'nullable' => $request['requireds'][$i] === 'yes' ? ' required' : '',
                                        'value' => '{{ isset($'.$modelNameSingularCamelCase.') && $'.$modelNameSingularCamelCase.'?->'.$fieldSnakeCase.' ? $'.$modelNameSingularCamelCase.'?->'.$fieldSnakeCase." : old(key: '".$fieldSnakeCase."') }}",
                                    ],
                                    stubName: 'views/forms/datalist'
                                );
                                break;
                            default:
                                // radio
                                $options .= "\t<div class=\"col-md-6\">\n\t<p>$fieldUcWords</p>\n";

                                foreach ($arrOption as $value) {
                                    $options .= GeneratorUtils::replaceStub(
                                        replaces: [
                                            'fieldSnakeCase' => $fieldSnakeCase,
                                            'optionKebabCase' => GeneratorUtils::singularKebabCase(string: $value),
                                            'value' => $value,
                                            'optionLowerCase' => GeneratorUtils::cleanSingularLowerCase($value),
                                            'checked' => '{{ isset($'.$modelNameSingularCamelCase.') && $'.$modelNameSingularCamelCase."?->$field == '$value' ? 'checked' : (old(key: '$field') == '$value' ? 'checked' : '') }}",
                                            'nullable' => $request['requireds'][$i] === 'yes' ? ' required' : '',
                                        ],
                                        stubName: 'views/forms/radio'
                                    );
                                }

                                $options .= "\t</div>\n";

                                $template .= $options;
                                break;
                        }

                        break;
                    case 'foreignId':
                        // remove '/' or sub folders
                        $constrainModel = GeneratorUtils::setModelName(model: $request['constrains'][$i], style: 'default');
                        $constrainSingularCamelCase = GeneratorUtils::singularCamelCase(string: $constrainModel);
                        $columnAfterId = GeneratorUtils::getColumnAfterId(table: $constrainModel);

                        $options = '
                        @foreach ($'.GeneratorUtils::pluralCamelCase(string: $constrainModel)." as $$constrainSingularCamelCase)
                            <option value=\"{{ $".$constrainSingularCamelCase."?->id }}\" {{ isset($$modelNameSingularCamelCase) && $".$modelNameSingularCamelCase."?->$fieldSnakeCase == $".$constrainSingularCamelCase."?->id ? 'selected' : (old(key: '$fieldSnakeCase') == $".$constrainSingularCamelCase."?->id ? 'selected' : '') }}>
                                {{ $".$constrainSingularCamelCase."?->$columnAfterId }}
                            </option>
                        @endforeach";

                        switch ($request['input_types'][$i]) {
                            case 'datalist':
                                $template .= GeneratorUtils::replaceStub(
                                    replaces: [
                                        'fieldKebabCase' => GeneratorUtils::KebabCase(string: $field),
                                        'fieldSnakeCase' => $fieldSnakeCase,
                                        'fieldUcWords' => GeneratorUtils::cleanSingularUcWords(string: $constrainModel),
                                        'fieldCamelCase' => GeneratorUtils::singularCamelCase(string: $field),
                                        'options' => $options,
                                        'nullable' => $request['requireds'][$i] === 'yes' ? ' required' : '',
                                        'value' => '{{ isset($'.$modelNameSingularCamelCase.') && $'.$modelNameSingularCamelCase.'?->'.$fieldSnakeCase.' ? $'.$modelNameSingularCamelCase.'?->'.$fieldSnakeCase." : old(key: '".$fieldSnakeCase."') }}",
                                    ],
                                    stubName: 'views/forms/datalist'
                                );
                                break;
                            default:
                                // select
                                $template .= GeneratorUtils::replaceStub(
                                    replaces: [
                                        'fieldKebabCase' => GeneratorUtils::singularKebabCase(string: $field),
                                        'fieldUcWords' => GeneratorUtils::cleanSingularUcWords(string: $constrainModel),
                                        'fieldSpaceLowercase' => GeneratorUtils::cleanSingularLowerCase(string: $constrainModel),
                                        'options' => $options,
                                        'nullable' => $request['requireds'][$i] === 'yes' ? ' required' : '',
                                        'fieldSnakeCase' => $fieldSnakeCase,
                                    ],
                                    stubName: 'views/forms/select'
                                );
                                break;
                        }
                        break;
                    case 'year':
                        $firstYear = is_int(config('generator.format.first_year')) ? config('generator.format.first_year') : 1970;

                        /**
                         * Will generate something like:
                         *
                         * <select class="form-select" name="year" id="year" class="form-control" required>
                         * <option value="" selected disabled>-- {{ __(key: 'Select year') }} --</option>
                         *
                         *  @foreach (start: range(1900, end: date(format: 'Y')) as $year)
                         *     <option value="{{ $year }}"
                         *        {{ isset($book) && $book->year == $year ? 'selected' : (old(key: 'year') == $year ? 'selected' : '') }}>
                         *      {{ $year }}
                         * </option>
                         *
                         *  @endforeach
                         * </select>
                         */
                        $options = "
                        @foreach (range(start: $firstYear, end: date(format: 'Y')) as \$year)
                            <option value=\"{{ \$year }}\" {{ isset($$modelNameSingularCamelCase) && $".$modelNameSingularCamelCase."?->$fieldSnakeCase == \$year ? 'selected' : (old(key: '$fieldSnakeCase') == \$year ? 'selected' : '') }}>
                                {{ \$year }}
                            </option>
                        @endforeach";

                        $template .= match ($request['input_types'][$i]) {
                            'datalist' => GeneratorUtils::replaceStub(
                                replaces: [
                                    'fieldKebabCase' => GeneratorUtils::singularKebabCase(string: $field),
                                    'fieldCamelCase' => GeneratorUtils::singularCamelCase(string: $field),
                                    'fieldUcWords' => $fieldUcWords,
                                    'fieldSnakeCase' => $fieldSnakeCase,
                                    'options' => $options,
                                    'nullable' => $request['requireds'][$i] === 'yes' ? ' required' : '',
                                    'value' => '{{ isset($'.$modelNameSingularCamelCase.') && $'.$modelNameSingularCamelCase.'?->'.$fieldSnakeCase.' ? $'.$modelNameSingularCamelCase.'?->'.$fieldSnakeCase." : old(key: '".$fieldSnakeCase."') }}",
                                ],
                                stubName: 'views/forms/datalist'
                            ),
                            default => GeneratorUtils::replaceStub(
                                replaces: [
                                    'fieldUcWords' => GeneratorUtils::cleanUcWords(string: $field),
                                    'fieldKebabCase' => GeneratorUtils::kebabCase(string: $field),
                                    'fieldSnakeCase' => $fieldSnakeCase,
                                    'fieldSpaceLowercase' => GeneratorUtils::cleanLowerCase(string: $field),
                                    'options' => $options,
                                    'nullable' => $request['requireds'][$i] === 'yes' ? ' required' : '',
                                ],
                                stubName: 'views/forms/select'
                            ),
                        };
                        break;
                    case 'boolean':
                        switch ($request['input_types'][$i]) {
                            case 'select':
                                // select
                                $options = '<option value="0" {{ isset($'.$modelNameSingularCamelCase.') && $'.$modelNameSingularCamelCase."?->$fieldSnakeCase == '0' ? 'selected' : (old(key: '$fieldSnakeCase') == '0' ? 'selected' : '') }}>{{ __(key: 'False') }}</option>\n\t\t\t\t<option value=\"1\" {{ isset($".$modelNameSingularCamelCase.') && $'.$modelNameSingularCamelCase."?->$fieldSnakeCase == '1' ? 'selected' : (old(key: '$fieldSnakeCase') == '1' ? 'selected' : '') }}>{{ __(key: 'True') }}</option>";

                                $template .= GeneratorUtils::replaceStub(
                                    replaces: [
                                        'fieldUcWords' => GeneratorUtils::cleanUcWords($field),
                                        'fieldSnakeCase' => $fieldSnakeCase,
                                        'fieldKebabCase' => GeneratorUtils::kebabCase($field),
                                        'fieldSpaceLowercase' => GeneratorUtils::cleanLowerCase($field),
                                        'options' => $options,
                                        'nullable' => $request['requireds'][$i] === 'yes' ? ' required' : '',
                                    ],
                                    stubName: 'views/forms/select'
                                );
                                break;

                            default:
                                // radio
                                $options = "\t<div class=\"col-md-6\">\n\t<p>$fieldUcWords</p>";

                                /**
                                 * will generate something like:
                                 *
                                 * <div class="form-check mb-2">
                                 *  <input class="form-check-input" type="radio" name="is_active" id="is_active-1" value="1" {{ isset($product) && $product->is_active == '1' ? 'checked' : (old(key: 'is_active') == '1' ? 'checked' : '') }}>
                                 *     <label class="form-check-label" for="is_active-1">True</label>
                                 * </div>
                                 *  <div class="form-check mb-2">
                                 *    <input class="form-check-input" type="radio" name="is_active" id="is_active-0" value="0" {{ isset($product) && $product->is_active == '0' ? 'checked' : (old(key: 'is_active') == '0' ? 'checked' : '') }}>
                                 *      <label class="form-check-label" for="is_active-0">False</label>
                                 * </div>
                                 */
                                $options .= "
                                <div class=\"form-check mb-2\">
                                    <input class=\"form-check-input\" type=\"radio\" name=\"$fieldSnakeCase\" id=\"$fieldSnakeCase-1\" value=\"1\" {{ isset($$modelNameSingularCamelCase) && $".$modelNameSingularCamelCase."?->$fieldSnakeCase == '1' ? 'checked' : (old(key: '$fieldSnakeCase') == '1' ? 'checked' : '') }}>
                                    <label class=\"form-check-label\" for=\"$fieldSnakeCase-1\">True</label>
                                </div>
                                <div class=\"form-check mb-2\">
                                    <input class=\"form-check-input\" type=\"radio\" name=\"$fieldSnakeCase\" id=\"$fieldSnakeCase-0\" value=\"0\" {{ isset($$modelNameSingularCamelCase) && $".$modelNameSingularCamelCase."?->$fieldSnakeCase == '0' ? 'checked' : (old(key: '$fieldSnakeCase') == '0' ? 'checked' : '') }}>
                                    <label class=\"form-check-label\" for=\"$fieldSnakeCase-0\">False</label>
                                </div>\n";

                                $options .= "\t</div>\n";

                                $template .= $options;
                                break;
                        }
                        break;

                    default:
                        // input form
                        if ($request['default_values'][$i]) {
                            $formatValue = "{{ (isset($$modelNameSingularCamelCase) ? $$modelNameSingularCamelCase->$fieldSnakeCase : old(key: '$fieldSnakeCase')) ? old(key: '$fieldSnakeCase') : '".$request['default_values'][$i]."' }}";
                        } else {
                            $formatValue = "{{ isset($$modelNameSingularCamelCase) ? $$modelNameSingularCamelCase->$fieldSnakeCase : old(key: '$fieldSnakeCase') }}";
                        }

                        switch ($request['input_types'][$i]) {
                            case 'datetime-local':
                                /**
                                 * Will generate something like:
                                 *
                                 * {{ isset($book) && $book->datetime ? $book->datetime->format('Y-m-d\TH:i') : old(key: 'datetime') }}
                                 */
                                $formatValue = "{{ isset($$modelNameSingularCamelCase) && $".$modelNameSingularCamelCase."?->$fieldSnakeCase ? $".$modelNameSingularCamelCase.'?->'.$fieldSnakeCase."?->format('Y-m-d\TH:i') : old(key: '$fieldSnakeCase') }}";

                                $template .= $this->setInputTypeTemplate(
                                    field: $field,
                                    request: [
                                        'input_types' => $request['input_types'][$i],
                                        'requireds' => $request['requireds'][$i],
                                    ],
                                    formatValue: $formatValue
                                );
                                break;
                            case 'date':
                                /**
                                 * Will generate something like:
                                 *
                                 * {{ isset($book) && $book->date ? $book->date->format('Y-m-d') : old(key: 'date') }}
                                 */
                                $formatValue = "{{ isset($$modelNameSingularCamelCase) && $".$modelNameSingularCamelCase."?->$fieldSnakeCase ? $".$modelNameSingularCamelCase.'?->'.$fieldSnakeCase."?->format('Y-m-d') : old(key: '$fieldSnakeCase') }}";

                                $template .= $this->setInputTypeTemplate(
                                    field: $field,
                                    request: [
                                        'input_types' => $request['input_types'][$i],
                                        'requireds' => $request['requireds'][$i],
                                    ],
                                    formatValue: $formatValue
                                );
                                break;
                            case 'time':
                                /**
                                 * Will generate something like:
                                 *
                                 * {{ isset($book) ? $book->time->format('H:i') : old(key: 'time') }}
                                 */
                                $formatValue = "{{ isset($$modelNameSingularCamelCase) && $".$modelNameSingularCamelCase."?->$fieldSnakeCase ? $".$modelNameSingularCamelCase.'?->'.$fieldSnakeCase."?->format('H:i') : old(key: '$fieldSnakeCase') }}";

                                $template .= $this->setInputTypeTemplate(
                                    field: $field,
                                    request: [
                                        'input_types' => $request['input_types'][$i],
                                        'requireds' => $request['requireds'][$i],
                                    ],
                                    formatValue: $formatValue
                                );
                                break;
                            case 'week':
                                /**
                                 * Will generate something like:
                                 *
                                 * {{ isset($book) ? $book->week->format('Y-\WW') : old(key: 'week') }}
                                 */
                                $formatValue = "{{ isset($$modelNameSingularCamelCase) && $".$modelNameSingularCamelCase."?->$fieldSnakeCase ? $".$modelNameSingularCamelCase.'?->'.$fieldSnakeCase."?->format('Y-\WW') : old(key: '$fieldSnakeCase') }}";

                                $template .= $this->setInputTypeTemplate(
                                    field: $field,
                                    request: [
                                        'input_types' => $request['input_types'][$i],
                                        'requireds' => $request['requireds'][$i],
                                    ],
                                    formatValue: $formatValue
                                );
                                break;
                            case 'month':
                                /**
                                 * Will generate something like:
                                 *
                                 * {{ isset($book) ? $book->month->format('Y-\WW') : old(key: 'month') }}
                                 */
                                $formatValue = "{{ isset($$modelNameSingularCamelCase) && $".$modelNameSingularCamelCase."?->$fieldSnakeCase ? $".$modelNameSingularCamelCase.'?->'.$fieldSnakeCase."?->format('Y-m') : old(key: '$fieldSnakeCase') }}";

                                $template .= $this->setInputTypeTemplate(
                                    field: $field,
                                    request: [
                                        'input_types' => $request['input_types'][$i],
                                        'requireds' => $request['requireds'][$i],
                                    ],
                                    formatValue: $formatValue
                                );
                                break;
                            case 'textarea':
                                $template .= GeneratorUtils::replaceStub(
                                    replaces: [
                                        'fieldKebabCase' => GeneratorUtils::kebabCase(string: $field),
                                        'fieldUppercase' => $fieldUcWords,
                                        'modelName' => $modelNameSingularCamelCase,
                                        'nullable' => $request['requireds'][$i] === 'yes' ? ' required' : '',
                                        'fieldSnakeCase' => $fieldSnakeCase,
                                    ],
                                    stubName: 'views/forms/textarea'
                                );
                                break;
                            case 'file':
                                $template .= GeneratorUtils::replaceStub(
                                    replaces: [
                                        'modelCamelCase' => $modelNameSingularCamelCase,
                                        'fieldSnakeCase' => str(string: $field)->snake()->toString(),
                                        'fieldLowercase' => GeneratorUtils::cleanSingularLowerCase(string: $field),
                                        'fieldUcWords' => $fieldUcWords,
                                        'nullable' => $request['requireds'][$i] == 'yes' ? ' required' : '',
                                        'fieldKebabCase' => GeneratorUtils::kebabCase(string: $field),
                                        'defaultImage' => config(key: 'generator.image.default', default: $request['default_values'][$i]),
                                    ],
                                    stubName: 'views/forms/image'
                                );
                                break;
                            case 'range':
                                $template .= GeneratorUtils::replaceStub(
                                    replaces: [
                                        'fieldSnakeCase' => GeneratorUtils::singularSnakeCase(string: $field),
                                        'fieldUcWords' => $fieldUcWords,
                                        'fieldKebabCase' => GeneratorUtils::singularKebabCase(string: $field),
                                        'nullable' => $request['requireds'][$i] === 'yes' ? ' required' : '',
                                        'min' => $request['min_lengths'][$i],
                                        'max' => $request['max_lengths'][$i],
                                        'step' => $request['steps'][$i] ? 'step="'.$request['steps'][$i].'"' : '',
                                    ],
                                    stubName: 'views/forms/range'
                                );
                                break;
                            case 'hidden':
                                $template .= '<input type="hidden" name="'.$fieldSnakeCase.'" value="'.$request['default_values'][$i].'">';
                                break;
                            case 'password':
                                $template .= GeneratorUtils::replaceStub(
                                    replaces: [
                                        'fieldUcWords' => $fieldUcWords,
                                        'fieldSnakeCase' => $fieldSnakeCase,
                                        'fieldKebabCase' => GeneratorUtils::singularKebabCase(string: $field),
                                        'model' => $modelNameSingularCamelCase,
                                        'isNullable' => $request['requireds'][$i] === 'yes' ? '{{ empty($'.GeneratorUtils::singularCamelCase(string: $model).") ? ' required' : '' }}" : '',
                                    ],
                                    stubName: 'views/forms/input-password'
                                );
                                break;
                            default:
                                $template .= $this->setInputTypeTemplate(
                                    field: $field,
                                    request: [
                                        'input_types' => $request['input_types'][$i],
                                        'requireds' => $request['requireds'][$i],
                                    ],
                                    formatValue: $formatValue
                                );
                                break;
                        }
                        break;
                }
            }
        }

        $template .= '</div>';

        // create a blade file
        if ($path) {
            $fullPath = resource_path(path: '/views/'.strtolower(string: $path)."/$modelNamePluralKebabCase/include");
            GeneratorUtils::checkFolder(path: $fullPath);
            file_put_contents(filename: $fullPath.'/form.blade.php', data: $template);
        } else {
            GeneratorUtils::checkFolder(path: resource_path(path: "/views/$modelNamePluralKebabCase/include"));
            file_put_contents(filename: resource_path(path: "/views/$modelNamePluralKebabCase/include/form.blade.php"), data: $template);
        }
    }

    /**
     * Set input type from .stub file.
     */
    public function setInputTypeTemplate(string $field, array $request, string $formatValue): string
    {
        return GeneratorUtils::replaceStub(
            replaces: [
                'fieldKebabCase' => GeneratorUtils::singularKebabCase(string: $field),
                'fieldUcWords' => GeneratorUtils::cleanUcWords(string: $field),
                'fieldSnakeCase' => str(string: $field)->snake(),
                'type' => $request['input_types'],
                'value' => $formatValue,
                'nullable' => $request['requireds'] == 'yes' ? ' required' : '',
            ],
            stubName: 'views/forms/input'
        );
    }
}
