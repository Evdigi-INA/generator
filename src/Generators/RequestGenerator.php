<?php

namespace EvdigiIna\Generator\Generators;

use EvdigiIna\Generator\Enums\GeneratorVariant;
use EvdigiIna\Generator\Enums\RequestGeneratorEnum;

class RequestGenerator
{
    /**
     * Generate a request validation class file.
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
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);
        $modelNamePluralPascalCase = GeneratorUtils::pluralPascalCase($model);
        $modelSingularPascalCase = GeneratorUtils::singularPascalCase($model);

        $namespace = $this->generateNamespace($path, $modelNamePluralPascalCase);
        $validations = $this->generateValidations($request, $model);

        $this->generateStoreRequest($namespace, $modelSingularPascalCase, $validations);
        $this->generateUpdateRequest($namespace, $modelSingularPascalCase, $validations, $request);
    }

    /**
     * Generate the namespace for the request classes.
     */
    protected function generateNamespace(string $path, string $model): string
    {
        return empty($path)
            ? "namespace App\Http\Requests\\$model;"
            : "namespace App\Http\Requests\\$path\\$model;";
    }

    /**
     * Generate validation rules for all fields.
     */
    protected function generateValidations(array $request, string $model): string
    {
        $validations = '';
        $totalFields = count($request['fields']);

        foreach ($request['fields'] as $i => $field) {
            $validations .= $this->buildFieldValidation($request, $model, $i, $field);

            if ($i + 1 != $totalFields) {
                $validations .= "\n\t\t\t";
            }
        }

        return $validations;
    }

    /**
     * Build validation rules for a single field.
     */
    protected function buildFieldValidation(array $request, string $model, int $index, string $field): string
    {
        $validation = "'".str($field)->snake()."' => ";
        $validation .= $request['requireds'][$index] == 'yes'
            ? '['.RequestGeneratorEnum::getRule('required')
            : '['.RequestGeneratorEnum::getRule('nullable');

        $validation = $this->addInputTypeValidations($validation, $request, $model, $index, $field);
        $validation = $this->addColumnTypeValidations($validation, $request, $index);
        $validation = $this->addLengthValidations($validation, $request, $index);

        // Remove trailing comma and close array
        return substr($validation, 0, -2).'], ';
    }

    /**
     * Add validations based on input type.
     */
    protected function addInputTypeValidations(string $validation, array $request, string $model, int $index, string $field): string
    {
        switch ($request['input_types'][$index]) {
            case 'url':
                return $validation.RequestGeneratorEnum::getRule('url');
            case 'email':
                if (GeneratorUtils::checkGeneratorVariant() != GeneratorVariant::SINGLE_FORM->value) {
                    return $validation.$this->buildUniqueRule($model, $field);
                }

                return $validation;
            case 'date':
                return $validation.RequestGeneratorEnum::getRule('date');
            case 'week':
                return $validation.RequestGeneratorEnum::getRule('week');
            case 'time':
                return $validation.RequestGeneratorEnum::getRule('time');
            case 'month':
                return $validation.RequestGeneratorEnum::getRule('month');
            case 'datetime-local':
                return $validation.RequestGeneratorEnum::getRule('datetime');
            case 'password':
                return $validation.RequestGeneratorEnum::getRule('password');
            case 'file':
                return $this->addFileValidations($validation, $request, $index);
            case 'text':
            case 'textarea':
                return $validation.RequestGeneratorEnum::getRule('string');
            case 'number':
            case 'range':
                return $validation.RequestGeneratorEnum::getRule('numeric');
            default:
                return $validation;
        }
    }

    /**
     * Build unique rule for email/unique fields.
     */
    protected function buildUniqueRule(string $model, string $field): string
    {
        return RequestGeneratorEnum::unique($model, $field);
    }

    /**
     * Add file specific validations.
     */
    protected function addFileValidations(string $validation, array $request, int $index): string
    {
        if ($request['file_types'][$index] == 'image') {
            return $this->buildImageValidation($validation, $request, $index);
        }

        if ($request['file_types'][$index] == 'mimes') {
            return $this->buildMimesValidation($validation, $request, $index);
        }

        return $validation;
    }

    /**
     * Build image validation string.
     */
    protected function buildImageValidation(string $validation, array $request, int $index): string
    {
        $maxSize = $request['files_sizes'][$index] ?? config('generator.image.size_max', 1024);

        return $validation.RequestGeneratorEnum::image($maxSize);
    }

    /**
     * Build mimes validation string.
     */
    protected function buildMimesValidation(string $validation, array $request, int $index): string
    {
        $mimes = implode(',', $request['mimes'][$index]);
        $size = $request['files_sizes'][$index];

        return $validation."'mimes:$mimes', 'size:$size', ";
    }

    /**
     * Add validations based on column type.
     */
    protected function addColumnTypeValidations(string $validation, array $request, int $index): string
    {
        switch ($request['column_types'][$index]) {
            case 'enum':
                return $validation.$this->buildEnumValidation($request['select_options'][$index]);
            case 'boolean':
                return $validation.RequestGeneratorEnum::getRule('boolean');
            case 'year':
                return $validation.RequestGeneratorEnum::getRule('year');
            case 'foreignId':
                return $this->addForeignKeyValidation($validation, $request, $index);
            default:
                return $validation;
        }
    }

    /**
     * Build validation for enum fields.
     */
    protected function buildEnumValidation(string $options): string
    {
        $options = explode('|', $options);

        return RequestGeneratorEnum::in($options);
    }

    /**
     * Add foreign key validation.
     */
    protected function addForeignKeyValidation(string $validation, array $request, int $index): string
    {
        $constrainModel = GeneratorUtils::setModelName($request['constrains'][$index]);
        $constrainsPath = GeneratorUtils::getModelLocation($request['constrains'][$index]);
        $replacedModel = str_replace('/', '\\', $constrainsPath);

        $table = $replacedModel ?: $constrainModel;

        return $validation.RequestGeneratorEnum::exists($table);
    }

    /**
     * Add length validations (min, max, between).
     */
    protected function addLengthValidations(string $validation, array $request, int $index): string
    {
        $min = $request['min_lengths'][$index] ?? 0;
        $max = $request['max_lengths'][$index] ?? 0;

        if ($this->shouldAddBetweenRule($request, $index)) {
            return $validation.RequestGeneratorEnum::between($min, $max);
        }

        if (in_array($request['column_types'][$index], ['string', 'text', 'char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext']) && $min && $max) {
            return $validation.RequestGeneratorEnum::min($min).RequestGeneratorEnum::max($max);
        }

        return $validation;
    }

    /**
     * Check if between rule should be added.
     */
    protected function shouldAddBetweenRule(array $request, int $index): bool
    {
        return $request['input_types'][$index] == 'range' && $request['max_lengths'][$index];
    }

    /**
     * Generate and save the store request file.
     */
    protected function generateStoreRequest(string $namespace, string $model, string $validations): void
    {
        $storeRequestTemplate = GeneratorUtils::replaceStub([
            'modelNamePascalCase' => "Store$model",
            'fields' => $validations,
            'namespace' => $namespace,
        ], 'request');

        $this->saveRequestFile("Store{$model}Request.php", $storeRequestTemplate, $model);
    }

    /**
     * Generate and save the update request file.
     */
    protected function generateUpdateRequest(string $namespace, string $model, string $validations, array $request): void
    {
        $updateValidations = $this->prepareUpdateValidations($validations, $request);

        $updateRequestTemplate = GeneratorUtils::replaceStub([
            'modelNamePascalCase' => "Update$model",
            'fields' => $updateValidations,
            'namespace' => $namespace,
        ], 'request');

        if (GeneratorUtils::checkGeneratorVariant() != GeneratorVariant::SINGLE_FORM->value) {
            $this->saveRequestFile("Update{$model}Request.php", $updateRequestTemplate, $model);
        }
    }

    /**
     * Prepare validations for update request.
     */
    protected function prepareUpdateValidations(string $validations, array $request): string
    {
        $updateValidations = $validations;
        $updateValidations = $this->handlePasswordFields($updateValidations, $request);
        $updateValidations = $this->handleImageFields($updateValidations, $request);

        return $this->handleEmailFields($updateValidations, $request);
    }

    /**
     * Handle password fields for update request.
     */
    protected function handlePasswordFields(string $validations, array $request): string
    {
        foreach ($request['input_types'] as $index => $input) {
            if ($input == 'password' && $request['requireds'][$index] == 'yes') {
                $validations = str_replace(
                    "'".$request['fields'][$index]."' => ['required'",
                    "'".$request['fields'][$index]."' => ['nullable'",
                    $validations
                );
            }
        }

        return $validations;
    }

    /**
     * Handle image fields for update request.
     */
    protected function handleImageFields(string $validations, array $request): string
    {
        foreach ($request['input_types'] as $index => $input) {
            if (
                isset($request['file_types'][$index]) &&
                $request['file_types'][$index] == 'image' &&
                $request['requireds'][$index] == 'yes'
            ) {
                $validations = str_replace(
                    "'".$request['fields'][$index]."' => ['required'",
                    "'".$request['fields'][$index]."' => ['nullable'",
                    $validations
                );
            }
        }

        return $validations;
    }

    /**
     * Handle email fields for update request.
     */
    protected function handleEmailFields(string $validations, array $request): string
    {
        $ignoreTemplate = "')->ignore(id: request()->segment(index: ".(GeneratorUtils::isGenerateApi() ? 3 : 2).'))';
        foreach ($request['input_types'] as $index => $input) {
            if ($input == 'email') {
                $validations = str_replace(
                    "column: '".str($request['fields'][$index])->snake()."')",
                    "column: '".str($request['fields'][$index])->snake().$ignoreTemplate,
                    $validations
                );
            }
        }

        return $validations;
    }

    /**
     * Save the request file to the appropriate location.
     */
    protected function saveRequestFile(string $filename, string $content, string $model): void
    {
        $modelPluralPascalCase = GeneratorUtils::pluralPascalCase($model);
        $path = app_path("/Http/Requests/$modelPluralPascalCase");
        GeneratorUtils::checkFolder($path);

        $content = $this->importRuleFilePasswordClass($content);

        file_put_contents("$path/$filename", $content);
    }

    /**
     * Ensures that the necessary Laravel validation classes are imported.
     */
    protected function importRuleFilePasswordClass(string $validation): string
    {
        $replacements = [
            '{{ruleImport}}' => str_contains($validation, 'Rule::')
                ? "use Illuminate\Validation\Rule;\n"
                : '',
            '{{passwordImport}}' => str_contains($validation, 'Password::')
                ? "use Illuminate\Validation\Rules\Password;\n"
                : '',
            '{{fileImport}}' => str_contains($validation, 'File::')
                ? "use Illuminate\Validation\Rules\File;\n"
                : '',
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $validation
        );
    }
}
