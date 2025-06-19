<?php

namespace EvdigiIna\Generator\Generators\Views;

use EvdigiIna\Generator\Generators\GeneratorUtils;

class FormViewGenerator
{
    protected const DEFAULT_FIRST_YEAR = 1970;

    /**
     * Generate a form/input for create and edit.
     *
     * @param array{
     *     model: string,
     *     fields: array<string>,
     *     requireds: array<string>,
     *     input_types: array<string>,
     *     file_types?: array<string>,
     *     files_sizes?: array<int>,
     *     mimes?: array<string>,
     *     column_types: array<string>,
     *     select_options?: array<string>,
     *     min_lengths: array<int>,
     *     max_lengths: array<int>,
     *     constrains?: array<string>
     * } $request
     */
    public function generate(array $request): void
    {
        $model = GeneratorUtils::setModelName(model: $request['model'], style: 'default');
        $path = GeneratorUtils::getModelLocation(model: $request['model']);

        $template = $this->buildFormTemplate(request: $request, modelNameSingularCamelCase: GeneratorUtils::singularCamelCase(string: $model));
        $this->saveFormTemplate(path: $path, modelNamePluralKebabCase: GeneratorUtils::pluralKebabCase(string: $model), template: $template);
    }

    /**
     * Build a form template string.
     */
    protected function buildFormTemplate(array $request, string $modelNameSingularCamelCase): string
    {
        $template = "<div class=\"row mb-2\">\n";

        foreach ($request['fields'] as $i => $field) {
            if ($request['input_types'][$i] !== 'no-input') {
                $template .= $this->processField(
                    request: $request,
                    index: $i,
                    field: $field,
                    modelNameSingularCamelCase: $modelNameSingularCamelCase
                );
            }
        }

        return $template.'</div>';
    }

    /**
     * Process a single field.
     */
    protected function processField(array $request, int $index, string $field, string $modelNameSingularCamelCase): string
    {
        $columnType = $request['column_types'][$index];

        return match ($columnType) {
            'enum' => $this->handleEnumField(request: $request, index: $index, field: $field, modelNameSingularCamelCase: $modelNameSingularCamelCase),
            'foreignId' => $this->handleForeignIdField(request: $request, index: $index, field: $field, modelNameSingularCamelCase: $modelNameSingularCamelCase),
            'year' => $this->handleYearField(request: $request, index: $index, field: $field, modelNameSingularCamelCase: $modelNameSingularCamelCase),
            'boolean' => $this->handleBooleanField(request: $request, index: $index, field: $field, modelNameSingularCamelCase: $modelNameSingularCamelCase),
            default => $this->handleDefaultField(request: $request, index: $index, field: $field, modelNameSingularCamelCase: $modelNameSingularCamelCase)
        };
    }

    /**
     * Process a single enum field.
     */
    protected function handleEnumField(array $request, int $index, string $field, string $modelNameSingularCamelCase): string
    {
        $arrOption = explode(separator: '|', string: $request['select_options'][$index]);
        $inputType = $request['input_types'][$index];
        $fieldSnakeCase = $this->getFieldSnakeCase(field: $field);

        $options = $this->buildEnumOptions(
            options: $arrOption,
            inputType: $inputType,
            fieldSnakeCase: $fieldSnakeCase,
            modelNameSingularCamelCase: $modelNameSingularCamelCase
        );

        return match ($inputType) {
            'select' => $this->generateSelectInput(field: $field, options: $options, required: $request['requireds'][$index]),
            'datalist' => $this->generateDatalistInput(field: $field, options: $options, required: $request['requireds'][$index], modelNameSingularCamelCase: $modelNameSingularCamelCase),
            default => $this->generateRadioInput(field: $field, options: $arrOption, required: $request['requireds'][$index], modelNameSingularCamelCase: $modelNameSingularCamelCase)
        };
    }

    /**
     * Builds the HTML options for an enum field based on the input type.
     */
    protected function buildEnumOptions(array $options, string $inputType, string $fieldSnakeCase, string $modelNameSingularCamelCase): string
    {
        $result = '';
        $totalOptions = count(value: $options);

        foreach ($options as $i => $value) {
            if (in_array(needle: $inputType, haystack: ['select', 'datalist'])) {
                $result .= GeneratorUtils::replaceStub(
                    replaces: [
                        'value' => $value,
                        'modelNameSingularCamelCase' => $modelNameSingularCamelCase,
                        'fieldSnakeCase' => $fieldSnakeCase,
                    ],
                    stubName: 'views/forms/option-enum',
                );
            } else {
                // For radio buttons, we handle it differently in generateRadioInput
                continue;
            }

            $result .= ($i + 1 != $totalOptions) ? "\n\t\t" : "\t\t\t";
        }

        return $result;
    }

    /**
     * Process a single foreign id field.
     */
    protected function handleForeignIdField(array $request, int $index, string $field, string $modelNameSingularCamelCase): string
    {
        $constrainModel = GeneratorUtils::setModelName(model: $request['constrains'][$index], style: 'default');
        $constrainSingularCamelCase = GeneratorUtils::singularCamelCase(string: $constrainModel);
        $columnAfterId = GeneratorUtils::getColumnAfterId(table: $constrainModel);
        $fieldSnakeCase = $this->getFieldSnakeCase(field: $field);

        $options = GeneratorUtils::replaceStub(
            replaces: [
                'constrainModelPluralCamelCase' => GeneratorUtils::pluralCamelCase(string: $constrainModel),
                'constrainModelSingularCamelCase' => $constrainSingularCamelCase,
                'columnAfterId' => $columnAfterId,
                'modelNameSingularCamelCase' => $modelNameSingularCamelCase,
                'fieldSnakeCase' => $fieldSnakeCase,
            ],
            stubName: 'views/forms/option-belongsto'
        );

        return match ($request['input_types'][$index]) {
            'datalist' => $this->generateDatalistInput(
                field: $field,
                options: $options,
                required: $request['requireds'][$index],
                modelNameSingularCamelCase: $modelNameSingularCamelCase
            ),
            default => $this->generateSelectInput(
                field: $field,
                options: $options,
                required: $request['requireds'][$index],
            )
        };
    }

    /**
     * Process a single year field.
     */
    protected function handleYearField(array $request, int $index, string $field, string $modelNameSingularCamelCase): string
    {
        $fieldSnakeCase = $this->getFieldSnakeCase(field: $field);
        $firstYear = is_int(value: config(key: 'generator.format.first_year'))
            ? config(key: 'generator.format.first_year')
            : self::DEFAULT_FIRST_YEAR;

        $options = GeneratorUtils::replaceStub(replaces: [
            'firstYear' => $firstYear,
            'modelNameSingularCamelCase' => $modelNameSingularCamelCase,
            'fieldSnakeCase' => $fieldSnakeCase,
        ], stubName: 'views/forms/option-year');

        return match ($request['input_types'][$index]) {
            'datalist' => $this->generateDatalistInput(
                field: $field,
                options: $options,
                required: $request['requireds'][$index],
                modelNameSingularCamelCase: $modelNameSingularCamelCase
            ),
            default => $this->generateSelectInput(
                field: $field,
                options: $options,
                required: $request['requireds'][$index],
            )
        };
    }

    /**
     * Process a single boolean field.
     */
    protected function handleBooleanField(array $request, int $index, string $field, string $modelNameSingularCamelCase
    ): string {
        return match ($request['input_types'][$index]) {
            'select' => $this->generateBooleanSelectInput(field: $field, modelNameSingularCamelCase: $modelNameSingularCamelCase, required: $request['requireds'][$index]),
            'datalist' => $this->generateBooleanDatalistInput(field: $field, modelNameSingularCamelCase: $modelNameSingularCamelCase, required: $request['requireds'][$index]),
            default => $this->generateBooleanRadioInput(field: $field, modelNameSingularCamelCase: $modelNameSingularCamelCase)
        };
    }

    /**
     * Process a single default field.
     *
     * Handles the following field types: datetime-local, date, time, week, month, textarea, file, range, hidden, password, and other string input types.
     */
    protected function handleDefaultField(array $request, int $index, string $field, string $modelNameSingularCamelCase): string
    {
        $fieldSnakeCase = $this->getFieldSnakeCase(field: $field);

        $formatValue = $this->getFormattedValue(
            defaultValue: $request['default_values'][$index],
            modelNameSingularCamelCase: $modelNameSingularCamelCase,
            fieldSnakeCase: $fieldSnakeCase
        );

        return match ($request['input_types'][$index]) {
            'datetime-local', 'date', 'time', 'week', 'month' => $this->handleDateTimeFields(
                inputType: $request['input_types'][$index],
                field: $field,
                modelNameSingularCamelCase: $modelNameSingularCamelCase,
                required: $request['requireds'][$index]
            ),
            'textarea' => $this->generateTextareaInput(field: $field, modelNameSingularCamelCase: $modelNameSingularCamelCase, required: $request['requireds'][$index]),
            'file' => $this->generateFileInput(field: $field, modelNameSingularCamelCase: $modelNameSingularCamelCase, required: $request['requireds'][$index], defaultValue: $request['default_values'][$index]),
            'range' => $this->generateRangeInput(field: $field, required: $request['requireds'][$index], min: $request['min_lengths'][$index], max: $request['max_lengths'][$index], step: $request['steps'][$index]),
            'hidden' => $this->generateHiddenInput(fieldSnakeCase: $fieldSnakeCase, defaultValue: $request['default_values'][$index]),
            'password' => $this->generatePasswordInput(field: $field, modelNameSingularCamelCase: $modelNameSingularCamelCase, required: $request['requireds'][$index]),
            default => $this->setInputTypeTemplate(
                field: $field,
                request: [
                    'input_types' => $request['input_types'][$index],
                    'requireds' => $request['requireds'][$index],
                ],
                formatValue: $formatValue
            )
        };
    }

    /**
     * Returns a formatted value based on the given default value and field name.
     * If the default value is not empty, it will be used as a fallback.
     * If the default value is empty, the old value will be used as a fallback.
     * If the old value is also empty, the field will be empty.
     */
    protected function getFormattedValue(?string $defaultValue, string $modelNameSingularCamelCase, string $fieldSnakeCase): string
    {
        $value = "{{ isset($$modelNameSingularCamelCase) ? $$modelNameSingularCamelCase->$fieldSnakeCase : old(key: '$fieldSnakeCase') }}";

        return $defaultValue
            ? "$value ? $value : '".$defaultValue."' }}"
            : $value;
    }

    /**
     * Handles datetime-local, date, time, week, and month field types.
     *
     * Returns a formatted value based on the given default value and field name.
     * If the default value is not empty, it will be used as a fallback.
     * If the default value is empty, the old value will be used as a fallback.
     * If the old value is also empty, the field will be empty.
     */
    protected function handleDateTimeFields(string $inputType, string $field, string $modelNameSingularCamelCase, string $required): string
    {
        $fieldSnakeCase = $this->getFieldSnakeCase(field: $field);
        $formatMap = [
            'datetime-local' => 'Y-m-d\TH:i',
            'date' => 'Y-m-d',
            'time' => 'H:i',
            'week' => 'Y-\WW',
            'month' => 'Y-m',
        ];

        $formatValue = "{{ isset($$modelNameSingularCamelCase) && $".$modelNameSingularCamelCase."?->$fieldSnakeCase ? $".$modelNameSingularCamelCase.'?->'.$fieldSnakeCase."?->format('".$formatMap[$inputType]."') : old(key: '$fieldSnakeCase') }}";

        return $this->setInputTypeTemplate(
            field: $field,
            request: [
                'input_types' => $inputType,
                'requireds' => $required,
            ],
            formatValue: $formatValue
        );
    }

    /**
     * Generate a select input with options.
     */
    protected function generateSelectInput(string $field, string $options, string $required): string
    {
        $fieldUcWords = GeneratorUtils::cleanUcWords(string: $field);
        // remove ID from fieldUcWords if from foreign key
        $fieldUcWords = str_replace(' Id', '', $fieldUcWords);

        return GeneratorUtils::replaceStub(
            replaces: [
                'options' => $options,
                'fieldUcWords' => $fieldUcWords,
                'fieldKebabCase' => $this->getFieldKebabCase(field: $field),
                'fieldSnakeCase' => $this->getFieldSnakeCase(field: $field),
                'fieldSpaceLowercase' => GeneratorUtils::cleanLowerCase(string: $field),
                'nullable' => $this->isRequired(required: $required),
            ],
            stubName: 'views/forms/select'
        );
    }

    /**
     * Generate a datalist input with options.
     */
    protected function generateDatalistInput(string $field, string $options, string $required, string $modelNameSingularCamelCase): string
    {
        $fieldUcWords = GeneratorUtils::cleanUcWords(string: $field);
        // remove ID from fieldUcWords if from foreign key
        $fieldUcWords = str_replace(' Id', '', $fieldUcWords);

        $fieldSnakeCase = $this->getFieldSnakeCase(field: $field);

        return GeneratorUtils::replaceStub(
            replaces: [
                'fieldUcWords' => $fieldUcWords,
                'fieldSnakeCase' => $fieldSnakeCase,
                'options' => $options,
                'fieldKebabCase' => $this->getFieldKebabCase(field: $field),
                'fieldCamelCase' => GeneratorUtils::singularCamelCase(string: $field),
                'nullable' => $this->isRequired(required: $required),
                'value' => '{{ isset($'.$modelNameSingularCamelCase.') && $'.$modelNameSingularCamelCase.'?->'.$fieldSnakeCase.' ? $'.$modelNameSingularCamelCase.'?->'.$fieldSnakeCase." : old(key: '".$fieldSnakeCase."') }}",
            ],
            stubName: 'views/forms/datalist'
        );
    }

    /**
     * Generate a radio input with options.
     */
    protected function generateRadioInput(string $field, array $options, string $required, string $modelNameSingularCamelCase): string
    {
        $result = "\t<div class=\"col-md-6\">\n\t<label>".GeneratorUtils::cleanUcWords(string: $field)."</label>\n";

        foreach ($options as $value) {
            $result .= GeneratorUtils::replaceStub(
                replaces: [
                    'value' => $value,
                    'optionKebabCase' => str(string: strtolower(string: $value))->kebab(),
                    'fieldSnakeCase' => $this->getFieldSnakeCase(field: $field),
                    'optionLowerCase' => GeneratorUtils::cleanSingularLowerCase(string: $value),
                    'checked' => '{{ isset($'.$modelNameSingularCamelCase.') && $'.$modelNameSingularCamelCase."?->$field == '$value' ? 'checked' : (old(key: '$field') == '$value' ? 'checked' : '') }}",
                    'nullable' => $this->isRequired(required: $required),
                ],
                stubName: 'views/forms/radio'
            );
        }

        return $result."\t</div>\n";
    }

    /**
     * Generate a select input with options for a boolean field.
     *
     * The options will be "Yes" and "No".
     * The selected value will be determined by the value of the given field in the model.
     * If the default value is empty, the old value will be used as a fallback.
     * If the old value is also empty, the field will be empty.
     */
    protected function generateBooleanSelectInput(string $field, string $modelNameSingularCamelCase, string $required): string
    {
        $options = GeneratorUtils::replaceStub(
            replaces: [
                'modelNameSingularCamelCase' => $modelNameSingularCamelCase,
                'fieldSnakeCase' => $this->getFieldSnakeCase(field: $field),
            ],
            stubName: 'views/forms/option-boolean'
        );

        return $this->generateSelectInput(
            field: $field,
            options: $options,
            required: $required,
        );
    }

    /**
     * Generate a datalist input with options for a boolean field.
     *
     * The options will be "Yes" and "No".
     * The selected value will be determined by the value of the given field in the model.
     * If the default value is empty, the old value will be used as a fallback.
     * If the old value is also empty, the field will be empty.
     */
    protected function generateBooleanDatalistInput(string $field, string $modelNameSingularCamelCase, string $required): string
    {
        $options = GeneratorUtils::replaceStub(
            replaces: [
                'modelNameSingularCamelCase' => $modelNameSingularCamelCase,
                'fieldSnakeCase' => $this->getFieldSnakeCase(field: $field),
            ],
            stubName: 'views/forms/option-boolean'
        );

        return $this->generateDatalistInput(
            field: $field,
            options: $options,
            required: $required,
            modelNameSingularCamelCase: $modelNameSingularCamelCase
        );
    }

    /**
     * Generate a radio input with options for a boolean field.
     *
     * The options will be "True" and "False".
     * The selected value will be determined by the value of the given field in the model.
     * If the default value is empty, the old value will be used as a fallback.
     * If the old value is also empty, the field will be empty.
     */
    protected function generateBooleanRadioInput(string $field, string $modelNameSingularCamelCase): string
    {
        return GeneratorUtils::replaceStub(replaces: [
            'fieldUcWords' => GeneratorUtils::cleanUcWords(string: $field),
            'fieldSnakeCase' => $this->getFieldSnakeCase(field: $field),
            'fieldKebabCase' => $this->getFieldKebabCase(field: $field),
            'modelNameSingularCamelCase' => $modelNameSingularCamelCase,
        ], stubName: 'views/forms/radio-boolean');
    }

    /**
     * Generate a textarea input field.
     *
     * This method generates a textarea input field with validation for a given
     * model attribute. It applies necessary transformations to the field name
     * and model name to fit the required template format.
     */
    protected function generateTextareaInput(string $field, string $modelNameSingularCamelCase, string $required): string
    {
        return GeneratorUtils::replaceStub(
            replaces: [
                'fieldKebabCase' => $this->getFieldKebabCase(field: $field),
                'fieldUppercase' => GeneratorUtils::cleanUcWords(string: $field),
                'modelName' => $modelNameSingularCamelCase,
                'nullable' => $this->isRequired(required: $required),
                'fieldSnakeCase' => $this->getFieldSnakeCase(field: $field),
            ],
            stubName: 'views/forms/textarea'
        );
    }

    /**
     * Generate a file input with validation for a given model attribute.
     *
     * This method generates a file input field with validation for a given
     * model attribute. It applies necessary transformations to the field name
     * and model name to fit the required template format.
     */
    protected function generateFileInput(string $field, string $modelNameSingularCamelCase, string $required, ?string $defaultValue): string
    {
        return GeneratorUtils::replaceStub(
            replaces: [
                'modelCamelCase' => $modelNameSingularCamelCase,
                'fieldSnakeCase' => $this->getFieldSnakeCase(field: $field),
                'fieldLowercase' => GeneratorUtils::cleanSingularLowerCase(string: $field),
                'fieldUcWords' => GeneratorUtils::cleanUcWords(string: $field),
                'nullable' => $required == 'yes' ? ' {{ isset($'.$modelNameSingularCamelCase."?->id) ? '' : ' required' }}" : '',
                'fieldKebabCase' => $this->getFieldKebabCase(field: $field),
                'defaultImage' => config(key: 'generator.image.default', default: $defaultValue),
            ],
            stubName: 'views/forms/image'
        );
    }

    /**
     * Generate a range input with options.
     *
     * The input will be a range type with min, max and step attributes.
     * The selected value will be determined by the value of the given field in the model.
     * If the default value is empty, the old value will be used as a fallback.
     * If the old value is also empty, the field will be empty.
     */
    protected function generateRangeInput(string $field, string $required, ?string $min, ?string $max, ?string $step): string
    {
        return GeneratorUtils::replaceStub(
            replaces: [
                'min' => $min,
                'max' => $max,
                'step' => $step ? 'step="'.$step.'"' : '',
                'fieldSnakeCase' => GeneratorUtils::singularSnakeCase(string: $field),
                'fieldUcWords' => GeneratorUtils::cleanUcWords(string: $field),
                'fieldKebabCase' => $this->getFieldKebabCase(field: $field),
                'nullable' => $this->isRequired(required: $required),
            ],
            stubName: 'views/forms/range'
        );
    }

    /**
     * Generate a hidden input with options.
     *
     * The input will be a hidden type and will have a default value.
     */
    protected function generateHiddenInput(string $fieldSnakeCase, ?string $defaultValue): string
    {
        return GeneratorUtils::replaceStub(
            replaces: [
                'fieldSnakeCase' => $fieldSnakeCase,
                'defaultValue' => $defaultValue,
            ],
            stubName: 'views/forms/hidden'
        );
    }

    /**
     * Generate a password input with options.
     *
     * The input will be a password type and will have a confirmation input.
     * The selected value will be determined by the value of the given field in the model.
     * If the default value is empty, the old value will be used as a fallback.
     * If the old value is also empty, the field will be empty.
     */
    protected function generatePasswordInput(string $field, string $required, string $modelNameSingularCamelCase
    ): string {
        return GeneratorUtils::replaceStub(
            replaces: [
                'model' => $modelNameSingularCamelCase,
                'fieldUcWords' => GeneratorUtils::cleanUcWords(string: $field),
                'fieldSnakeCase' => $this->getFieldSnakeCase(field: $field),
                'fieldKebabCase' => $this->getFieldKebabCase(field: $field),
                'isNullable' => $required === 'yes' ? '{{ empty($'.GeneratorUtils::singularCamelCase(string: $modelNameSingularCamelCase).") ? ' required' : '' }}" : '',
            ],
            stubName: 'views/forms/password'
        );
    }

    /**
     * Save the form template.
     */
    protected function saveFormTemplate(?string $path, string $modelNamePluralKebabCase, string $template): void
    {
        $fullPath = $path
            ? resource_path(path: '/views/'.strtolower(string: $path)."/$modelNamePluralKebabCase/include")
            : resource_path(path: "/views/$modelNamePluralKebabCase/include");

        GeneratorUtils::checkFolder(path: $fullPath);
        file_put_contents(filename: $fullPath.'/form.blade.php', data: $template);
    }

    /**
     * Set input type from .stub file.
     */
    public function setInputTypeTemplate(string $field, array $request, string $formatValue): string
    {
        return GeneratorUtils::replaceStub(
            replaces: [
                'fieldKebabCase' => $this->getFieldKebabCase(field: $field),
                'fieldUcWords' => GeneratorUtils::cleanUcWords(string: $field),
                'fieldSnakeCase' => $this->getFieldSnakeCase(field: $field),
                'nullable' => $this->isRequired(required: $request['requireds']),
                'type' => $request['input_types'],
                'value' => $formatValue,
            ],
            stubName: 'views/forms/input'
        );
    }

    /**
     * Return the given field as snake case.
     */
    protected function getFieldSnakeCase(string $field): string
    {
        return str(string: $field)->snake();
    }

    /**
     * Return the given field as kebab case.
     */
    protected function getFieldKebabCase(string $field): string
    {
        return str(string: $field)->kebab();
    }

    /**
     * Check if the given field is required.
     */
    protected function isRequired(string $required): string
    {
        return $required === 'yes' ? ' required' : '';
    }
}
