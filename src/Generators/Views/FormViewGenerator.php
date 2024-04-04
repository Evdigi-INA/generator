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
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);

        $modelNameSingularCamelCase = GeneratorUtils::singularCamelCase($model);
        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase($model);

        $template = "<div class=\"row mb-2\">\n";

        foreach ($request['fields'] as $i => $field) {

            if ($request['input_types'][$i] !== 'no-input') {
                $fieldSnakeCase = str($field)->snake();
                $fieldUcWords = GeneratorUtils::cleanUcWords($field);

                switch ($request['column_types'][$i]) {
                    case 'enum':
                        $options = "";

                        $arrOption = explode('|', $request['select_options'][$i]);

                        $totalOptions = count($arrOption);

                        switch ($request['input_types'][$i]) {
                            case 'select':
                                // select
                                foreach ($arrOption as $arrOptionIndex => $value) {
                                    $options .= <<<BLADE
                                    <option value="$value" {{ isset(\$$modelNameSingularCamelCase) && \$$modelNameSingularCamelCase->$fieldSnakeCase == '$value' ? 'selected' : (old('$fieldSnakeCase') == '$value' ? 'selected' : '') }}>$value</option>
                                    BLADE;

                                    if ($arrOptionIndex + 1 != $totalOptions) {
                                        $options .= "\n\t\t";
                                    } else {
                                        $options .= "\t\t\t";
                                    }
                                }

                                $template .= str_replace(
                                    [
                                        '{{fieldUcWords}}',
                                        '{{fieldKebabCase}}',
                                        '{{fieldSnakeCase}}',
                                        '{{fieldSpaceLowercase}}',
                                        '{{options}}',
                                        '{{nullable}}'
                                    ],
                                    [
                                        $fieldUcWords,
                                        GeneratorUtils::kebabCase($field),
                                        $fieldSnakeCase,
                                        GeneratorUtils::cleanLowerCase($field),
                                        $options,
                                        $request['requireds'][$i] == 'yes' ? ' required' : '',
                                    ],
                                    GeneratorUtils::getStub('views/forms/select')
                                );
                                break;
                            case 'datalist':
                                foreach ($arrOption as $arrOptionIndex => $value) {
                                    $options .= "<option value=\"" . $value . "\">$value</option>";

                                    if ($arrOptionIndex + 1 != $totalOptions) {
                                        $options .= "\n\t\t";
                                    } else {
                                        $options .= "\t\t\t";
                                    }
                                }

                                $template .= str_replace(
                                    [
                                        '{{fieldKebabCase}}',
                                        '{{fieldCamelCase}}',
                                        '{{fieldUcWords}}',
                                        '{{fieldSnakeCase}}',
                                        '{{options}}',
                                        '{{nullable}}',
                                        '{{value}}',
                                    ],
                                    [
                                        GeneratorUtils::kebabCase($field),
                                        GeneratorUtils::singularCamelCase($field),
                                        $fieldUcWords,
                                        $fieldSnakeCase,
                                        $options,
                                        $request['requireds'][$i] == 'yes' ? ' required' : '',
                                        "{{ isset($" . $modelNameSingularCamelCase . ") && $" . $modelNameSingularCamelCase . "?->" . $fieldSnakeCase . " ? $" . $modelNameSingularCamelCase . "?->" . $fieldSnakeCase . " : old('" . $fieldSnakeCase . "') }}"
                                    ],
                                    GeneratorUtils::getStub('views/forms/datalist')
                                );
                                break;
                            default:
                                // radio
                                $options .= "\t<div class=\"col-md-6\">\n\t<p>$fieldUcWords</p>\n";

                                foreach ($arrOption as $value) {
                                    $options .= str_replace(
                                        [
                                            '{{fieldSnakeCase}}',
                                            '{{optionKebabCase}}',
                                            '{{value}}',
                                            '{{optionLowerCase}}',
                                            '{{checked}}',
                                            '{{nullable}}'
                                        ],
                                        [
                                            $fieldSnakeCase,
                                            GeneratorUtils::singularKebabCase($value),
                                            $value,
                                            GeneratorUtils::cleanSingularLowerCase($value),
                                            "{{ isset($" . $modelNameSingularCamelCase . ") && $" . $modelNameSingularCamelCase . "?->$field == '$value' ? 'checked' : (old('$field') == '$value' ? 'checked' : '') }}",
                                            $request['requireds'][$i] == 'yes' ? ' required' : '',
                                        ],
                                        GeneratorUtils::getStub('views/forms/radio')
                                    );
                                }

                                $options .= "\t</div>\n";

                                $template .= $options;
                                break;
                        }

                        break;
                    case 'foreignId':
                        // remove '/' or sub folders
                        $constrainModel = GeneratorUtils::setModelName($request['constrains'][$i], 'default');

                        $constrainSingularCamelCase = GeneratorUtils::singularCamelCase($constrainModel);

                        $columnAfterId = GeneratorUtils::getColumnAfterId($constrainModel);

                        $options = "
                        @foreach ($" .  GeneratorUtils::pluralCamelCase($constrainModel) . " as $$constrainSingularCamelCase)
                            <option value=\"{{ $" . $constrainSingularCamelCase . "?->id }}\" {{ isset($$modelNameSingularCamelCase) && $" . $modelNameSingularCamelCase . "?->$fieldSnakeCase == $" . $constrainSingularCamelCase . "?->id ? 'selected' : (old('$fieldSnakeCase') == $" . $constrainSingularCamelCase . "?->id ? 'selected' : '') }}>
                                {{ $" . $constrainSingularCamelCase . "?->$columnAfterId }}
                            </option>
                        @endforeach";

                        switch ($request['input_types'][$i]) {
                            case 'datalist':
                                $template .= str_replace(
                                    [
                                        '{{fieldKebabCase}}',
                                        '{{fieldSnakeCase}}',
                                        '{{fieldUcWords}}',
                                        '{{fieldCamelCase}}',
                                        '{{options}}',
                                        '{{nullable}}',
                                        '{{value}}',
                                    ],
                                    [
                                        GeneratorUtils::KebabCase($field),
                                        $fieldSnakeCase,
                                        GeneratorUtils::cleanSingularUcWords($constrainModel),
                                        GeneratorUtils::singularCamelCase($field),
                                        $options,
                                        $request['requireds'][$i] == 'yes' ? ' required' : '',
                                        "{{ isset($" . $modelNameSingularCamelCase . ") && $" . $modelNameSingularCamelCase . "?->" . $fieldSnakeCase . " ? $" . $modelNameSingularCamelCase . "?->" . $fieldSnakeCase . " : old('" . $fieldSnakeCase . "') }}"
                                    ],
                                    GeneratorUtils::getStub('views/forms/datalist')
                                );
                                break;
                            default:
                                // select
                                $template .= str_replace(
                                    [
                                        '{{fieldKebabCase}}',
                                        '{{fieldUcWords}}',
                                        '{{fieldSpaceLowercase}}',
                                        '{{options}}',
                                        '{{nullable}}',
                                        '{{fieldSnakeCase}}'
                                    ],
                                    [
                                        GeneratorUtils::singularKebabCase($field),
                                        GeneratorUtils::cleanSingularUcWords($constrainModel),
                                        GeneratorUtils::cleanSingularLowerCase($constrainModel),
                                        $options,
                                        $request['requireds'][$i] == 'yes' ? ' required' : '',
                                        $fieldSnakeCase
                                    ],
                                    GeneratorUtils::getStub('views/forms/select')
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
                         * <option value="" selected disabled>-- {{ __('Select year') }} --</option>
                         *  @foreach (range(1900, strftime('%Y', time())) as $year)
                         *     <option value="{{ $year }}"
                         *        {{ isset($book) && $book->year == $year ? 'selected' : (old('year') == $year ? 'selected' : '') }}>
                         *      {{ $year }}
                         * </option>
                         *  @endforeach
                         * </select>
                         */
                        $options = "
                        @foreach (range($firstYear, strftime(\"%Y\", time())) as \$year)
                            <option value=\"{{ \$year }}\" {{ isset($$modelNameSingularCamelCase) && $" . $modelNameSingularCamelCase . "?->$fieldSnakeCase == \$year ? 'selected' : (old('$fieldSnakeCase') == \$year ? 'selected' : '') }}>
                                {{ \$year }}
                            </option>
                        @endforeach";

                        switch ($request['input_types'][$i]) {
                            case 'datalist':
                                $template .= str_replace(
                                    [
                                        '{{fieldKebabCase}}',
                                        '{{fieldCamelCase}}',
                                        '{{fieldUcWords}}',
                                        '{{fieldSnakeCase}}',
                                        '{{options}}',
                                        '{{nullable}}',
                                        '{{value}}',
                                    ],
                                    [
                                        GeneratorUtils::singularKebabCase($field),
                                        GeneratorUtils::singularCamelCase($field),
                                        $fieldUcWords,
                                        $fieldSnakeCase,
                                        $options,
                                        $request['requireds'][$i] == 'yes' ? ' required' : '',
                                        "{{ isset($" . $modelNameSingularCamelCase . ") && $" . $modelNameSingularCamelCase . "?->" . $fieldSnakeCase . " ? $" . $modelNameSingularCamelCase . "?->" . $fieldSnakeCase . " : old('" . $fieldSnakeCase . "') }}"
                                    ],
                                    GeneratorUtils::getStub('views/forms/datalist')
                                );
                                break;
                            default:
                                $template .= str_replace(
                                    [
                                        '{{fieldUcWords}}',
                                        '{{fieldKebabCase}}',
                                        '{{fieldSnakeCase}}',
                                        '{{fieldSpaceLowercase}}',
                                        '{{options}}',
                                        '{{nullable}}'
                                    ],
                                    [
                                        GeneratorUtils::cleanUcWords($field),
                                        GeneratorUtils::kebabCase($field),
                                        $fieldSnakeCase,
                                        GeneratorUtils::cleanLowerCase($field),
                                        $options,
                                        $request['requireds'][$i] == 'yes' ? ' required' : '',
                                    ],
                                    GeneratorUtils::getStub('views/forms/select')
                                );
                                break;
                        }
                        break;
                    case 'boolean':
                        switch ($request['input_types'][$i]) {
                            case 'select':
                                // select
                                $options = "<option value=\"0\" {{ isset($" . $modelNameSingularCamelCase . ") && $" . $modelNameSingularCamelCase . "?->$fieldSnakeCase == '0' ? 'selected' : (old('$fieldSnakeCase') == '0' ? 'selected' : '') }}>{{ __('True') }}</option>\n\t\t\t\t<option value=\"1\" {{ isset($" . $modelNameSingularCamelCase . ") && $" . $modelNameSingularCamelCase . "?->$fieldSnakeCase == '1' ? 'selected' : (old('$fieldSnakeCase') == '1' ? 'selected' : '') }}>{{ __('False') }}</option>";

                                $template .= str_replace(
                                    [
                                        '{{fieldUcWords}}',
                                        '{{fieldSnakeCase}}',
                                        '{{fieldKebabCase}}',
                                        '{{fieldSpaceLowercase}}',
                                        '{{options}}',
                                        '{{nullable}}'
                                    ],
                                    [
                                        GeneratorUtils::cleanUcWords($field),
                                        $fieldSnakeCase,
                                        GeneratorUtils::kebabCase($field),
                                        GeneratorUtils::cleanLowerCase($field),
                                        $options,
                                        $request['requireds'][$i] == 'yes' ? ' required' : '',
                                    ],
                                    GeneratorUtils::getStub('views/forms/select')
                                );
                                break;

                            default:
                                // radio
                                $options = "\t<div class=\"col-md-6\">\n\t<p>$fieldUcWords</p>";

                                /**
                                 * will generate something like:
                                 *
                                 * <div class="form-check mb-2">
                                 *  <input class="form-check-input" type="radio" name="is_active" id="is_active-1" value="1" {{ isset($product) && $product->is_active == '1' ? 'checked' : (old('is_active') == '1' ? 'checked' : '') }}>
                                 *     <label class="form-check-label" for="is_active-1">True</label>
                                 * </div>
                                 *  <div class="form-check mb-2">
                                 *    <input class="form-check-input" type="radio" name="is_active" id="is_active-0" value="0" {{ isset($product) && $product->is_active == '0' ? 'checked' : (old('is_active') == '0' ? 'checked' : '') }}>
                                 *      <label class="form-check-label" for="is_active-0">False</label>
                                 * </div>
                                 */
                                $options .= "
                                <div class=\"form-check mb-2\">
                                    <input class=\"form-check-input\" type=\"radio\" name=\"$fieldSnakeCase\" id=\"$fieldSnakeCase-1\" value=\"1\" {{ isset($$modelNameSingularCamelCase) && $" . $modelNameSingularCamelCase . "?->$fieldSnakeCase == '1' ? 'checked' : (old('$fieldSnakeCase') == '1' ? 'checked' : '') }}>
                                    <label class=\"form-check-label\" for=\"$fieldSnakeCase-1\">True</label>
                                </div>
                                <div class=\"form-check mb-2\">
                                    <input class=\"form-check-input\" type=\"radio\" name=\"$fieldSnakeCase\" id=\"$fieldSnakeCase-0\" value=\"0\" {{ isset($$modelNameSingularCamelCase) && $" . $modelNameSingularCamelCase . "?->$fieldSnakeCase == '0' ? 'checked' : (old('$fieldSnakeCase') == '0' ? 'checked' : '') }}>
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
                            $formatValue =  "{{ (isset($$modelNameSingularCamelCase) ? $$modelNameSingularCamelCase->$fieldSnakeCase : old('$fieldSnakeCase')) ? old('$fieldSnakeCase') : '" . $request['default_values'][$i] . "' }}";
                        } else {
                            $formatValue = "{{ isset($$modelNameSingularCamelCase) ? $$modelNameSingularCamelCase->$fieldSnakeCase : old('$fieldSnakeCase') }}";
                        }

                        switch ($request['input_types'][$i]) {
                            case 'datetime-local':
                                /**
                                 * Will generate something like:
                                 *
                                 * {{ isset($book) && $book->datetime ? $book->datetime->format('Y-m-d\TH:i') : old('datetime') }}
                                 */
                                $formatValue = "{{ isset($$modelNameSingularCamelCase) && $" . $modelNameSingularCamelCase . "?->$fieldSnakeCase ? $" . $modelNameSingularCamelCase . "?->" . $fieldSnakeCase . "?->format('Y-m-d\TH:i') : old('$fieldSnakeCase') }}";

                                $template .= $this->setInputTypeTemplate(
                                    field: $field,
                                    request: [
                                        'input_types' => $request['input_types'][$i],
                                        'requireds' => $request['requireds'][$i]
                                    ],
                                    formatValue: $formatValue
                                );
                                break;
                            case 'date':
                                /**
                                 * Will generate something like:
                                 *
                                 * {{ isset($book) && $book->date ? $book->date->format('Y-m-d') : old('date') }}
                                 */
                                $formatValue = "{{ isset($$modelNameSingularCamelCase) && $" . $modelNameSingularCamelCase . "?->$fieldSnakeCase ? $" . $modelNameSingularCamelCase . "?->" . $fieldSnakeCase . "?->format('Y-m-d') : old('$fieldSnakeCase') }}";

                                $template .= $this->setInputTypeTemplate(
                                    field: $field,
                                    request: [
                                        'input_types' => $request['input_types'][$i],
                                        'requireds' => $request['requireds'][$i]
                                    ],
                                    formatValue: $formatValue
                                );
                                break;
                            case 'time':
                                /**
                                 * Will generate something like:
                                 *
                                 * {{ isset($book) ? $book->time->format('H:i') : old('time') }}
                                 */
                                $formatValue = "{{ isset($$modelNameSingularCamelCase) && $" . $modelNameSingularCamelCase . "?->$fieldSnakeCase ? $" . $modelNameSingularCamelCase . "?->" . $fieldSnakeCase . "?->format('H:i') : old('$fieldSnakeCase') }}";

                                $template .= $this->setInputTypeTemplate(
                                    field: $field,
                                    request: [
                                        'input_types' => $request['input_types'][$i],
                                        'requireds' => $request['requireds'][$i]
                                    ],
                                    formatValue: $formatValue
                                );
                                break;
                            case 'week':
                                /**
                                 * Will generate something like:
                                 *
                                 * {{ isset($book) ? $book->week->format('Y-\WW') : old('week') }}
                                 */
                                $formatValue = "{{ isset($$modelNameSingularCamelCase) && $" . $modelNameSingularCamelCase . "?->$fieldSnakeCase ? $" . $modelNameSingularCamelCase . "?->" . $fieldSnakeCase . "?->format('Y-\WW') : old('$fieldSnakeCase') }}";

                                $template .= $this->setInputTypeTemplate(
                                    field: $field,
                                    request: [
                                        'input_types' => $request['input_types'][$i],
                                        'requireds' => $request['requireds'][$i]
                                    ],
                                    formatValue: $formatValue
                                );
                                break;
                            case 'month':
                                /**
                                 * Will generate something like:
                                 *
                                 * {{ isset($book) ? $book->month->format('Y-\WW') : old('month') }}
                                 */
                                $formatValue = "{{ isset($$modelNameSingularCamelCase) && $" . $modelNameSingularCamelCase . "?->$fieldSnakeCase ? $" . $modelNameSingularCamelCase . "?->" . $fieldSnakeCase . "?->format('Y-m') : old('$fieldSnakeCase') }}";

                                $template .= $this->setInputTypeTemplate(
                                    field: $field,
                                    request: [
                                        'input_types' => $request['input_types'][$i],
                                        'requireds' => $request['requireds'][$i]
                                    ],
                                    formatValue: $formatValue
                                );
                                break;
                            case 'textarea':
                                // textarea
                                $template .= str_replace(
                                    [
                                        '{{fieldKebabCase}}',
                                        '{{fieldUppercase}}',
                                        '{{modelName}}',
                                        '{{nullable}}',
                                        '{{fieldSnakeCase}}'
                                    ],
                                    [
                                        GeneratorUtils::kebabCase($field),
                                        $fieldUcWords,
                                        $modelNameSingularCamelCase,
                                        $request['requireds'][$i] == 'yes' ? ' required' : '',
                                        $fieldSnakeCase
                                    ],
                                    GeneratorUtils::getStub('views/forms/textarea')
                                );
                                break;
                            case 'file':
                                $default = GeneratorUtils::setDefaultImage(
                                    default: $request['default_values'][$i],
                                    field: $field,
                                    model: $model
                                );

                                $template .= str_replace(
                                    [
                                        '{{modelCamelCase}}',
                                        '{{fieldPluralSnakeCase}}',
                                        '{{fieldSnakeCase}}',
                                        '{{fieldLowercase}}',
                                        '{{fieldUcWords}}',
                                        '{{nullable}}',
                                        '{{uploadPathPublic}}',
                                        '{{fieldKebabCase}}',
                                        '{{defaultImage}}',
                                        '{{defaultImageCodeForm}}',
                                        '{{setDiskForCastImage}}'
                                    ],
                                    [
                                        $modelNameSingularCamelCase,
                                        GeneratorUtils::pluralSnakeCase($field),
                                        str()->snake($field),
                                        GeneratorUtils::cleanSingularLowerCase($field),
                                        $fieldUcWords,
                                        $request['requireds'][$i] == 'yes' ? ' required' : '',
                                        config('generator.image.disk') == 'storage' ? "storage/uploads" : "uploads",
                                        str()->kebab($field),
                                        $default['image'],
                                        $default['form_code'],
                                        /*
                                         * when disk is s3 it will generate something like: \Illuminate\Support\Facades\Storage::disk('s3')->url($this->photoPath . $cat->photo)
                                         * $this->photoPath, not available in view then change to 'photos/'
                                         */
                                        str_replace("\$this->".  GeneratorUtils::singularCamelCase($field) ."Path", "'". GeneratorUtils::pluralKebabCase($field) ."/'", GeneratorUtils::setDiskCodeForCastImage($model, $field) )
                                    ],
                                    GeneratorUtils::getStub('views/forms/image')
                                );
                                break;
                            case 'range':
                                $template .= str_replace(
                                    [
                                        '{{fieldSnakeCase}}',
                                        '{{fieldUcWords}}',
                                        '{{fieldKebabCase}}',
                                        '{{nullable}}',
                                        '{{min}}',
                                        '{{max}}',
                                        '{{step}}'
                                    ],
                                    [
                                        GeneratorUtils::singularSnakeCase($field),
                                        $fieldUcWords,
                                        GeneratorUtils::singularKebabCase($field),
                                        $request['requireds'][$i] == 'yes' ? ' required' : '',
                                        $request['min_lengths'][$i],
                                        $request['max_lengths'][$i],
                                        $request['steps'][$i] ? 'step="' . $request['steps'][$i] . '"' : '',
                                    ],
                                    GeneratorUtils::getStub('views/forms/range')
                                );
                                break;
                            case 'hidden':
                                $template .= '<input type="hidden" name="' . $fieldSnakeCase . '" value="' . $request['default_values'][$i] . '">';
                                break;
                            case 'password':
                                $template .= str_replace(
                                    [
                                        '{{fieldUcWords}}',
                                        '{{fieldSnakeCase}}',
                                        '{{fieldKebabCase}}',
                                        '{{model}}'
                                    ],
                                    [
                                        $fieldUcWords,
                                        $fieldSnakeCase,
                                        GeneratorUtils::singularKebabCase($field),
                                        $modelNameSingularCamelCase
                                    ],
                                    GeneratorUtils::getStub('views/forms/input-password')
                                );
                                break;
                            default:
                                $template .= $this->setInputTypeTemplate(
                                    field: $field,
                                    request: [
                                        'input_types' => $request['input_types'][$i],
                                        'requireds' => $request['requireds'][$i]
                                    ],
                                    formatValue: $formatValue
                                );
                                break;
                        }
                        break;
                }
            }
        }

        $template .= "</div>";

        // create a blade file
        switch ($path) {
            case '':
                GeneratorUtils::checkFolder(resource_path("/views/$modelNamePluralKebabCase/include"));
                file_put_contents(resource_path("/views/$modelNamePluralKebabCase/include/form.blade.php"), $template);
                break;
            default:
                $fullPath = resource_path("/views/" . strtolower($path) . "/$modelNamePluralKebabCase/include");
                GeneratorUtils::checkFolder($fullPath);
                file_put_contents($fullPath . "/form.blade.php", $template);
                break;
        }
    }

    /**
     * Set input type from .stub file.
     */
    public function setInputTypeTemplate(string $field, array $request, string $formatValue): string
    {
        return str_replace(
            [
                '{{fieldKebabCase}}',
                '{{fieldUcWords}}',
                '{{fieldSnakeCase}}',
                '{{type}}',
                '{{value}}',
                '{{nullable}}',
            ],
            [
                GeneratorUtils::singularKebabCase($field),
                GeneratorUtils::cleanUcWords($field),
                str($field)->snake(),
                $request['input_types'],
                $formatValue,
                $request['requireds'] == 'yes' ? ' required' : '',
            ],
            GeneratorUtils::getStub('views/forms/input')
        );
    }
}
