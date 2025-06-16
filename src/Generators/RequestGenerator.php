<?php

namespace EvdigiIna\Generator\Generators;

use EvdigiIna\Generator\Enums\GeneratorVariant;

class RequestGenerator
{
    // Basic validation rules
    protected const RULE_URL = "'url', ";
    protected const RULE_DATE = "Rule::date(), ";
    protected const RULE_BOOLEAN = "'boolean', ";
    protected const RULE_STRING = "'string', ";
    protected const RULE_NUMERIC = "Rule::numeric(), ";
    protected const RULE_CONFIRMED = "'confirmed', ";
    protected const RULE_EMAIL = "Rule::email(), ";
    protected const RULE_PASSWORD_MIN = "Password::min(size: 8), ";
    protected const RULE_IMAGE = "File::image()";
    protected const RULE_REQUIRED = "'required', ";
    protected const RULE_NULLABLE = "'nullable', ";

    // Rule builders
    protected const RULE_IMAGE_MAX = "->max(size: ";
    protected const RULE_IMAGE_CLOSE = "), ";
    protected const RULE_EXISTS_OPEN = "Rule::exists(table: '";
    protected const RULE_EXISTS_CLOSE = "', column: 'id'), ";
    protected const RULE_UNIQUE_OPEN = "Rule::unique(table: '";
    protected const RULE_UNIQUE_MIDDLE = "', column: '";
    protected const RULE_UNIQUE_CLOSE = "'), ";
    protected const RULE_IN_OPEN = "Rule::in(values: [";
    protected const RULE_IN_CLOSE = "]), ";
    protected const RULE_BETWEEN = "'between:";
    protected const RULE_MIN = "'min:";
    protected const RULE_MAX = "'max:";
    protected const RULE_LENGTH_CLOSE = "', ";

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
        $validation = "'" . str($field)->snake() . "' => ";
        $validation .= $request['requireds'][$index] == 'yes'
            ? "[" . self::RULE_REQUIRED
            : "[" . self::RULE_NULLABLE;

        $validation = $this->addInputTypeValidations($validation, $request, $model, $index, $field);
        $validation = $this->addColumnTypeValidations($validation, $request, $index);
        $validation = $this->addLengthValidations($validation, $request, $index);

        // Remove trailing comma and close array
        return substr($validation, 0, -2) . "], ";
    }

    /**
     * Add validations based on input type.
     */
    protected function addInputTypeValidations(string $validation, array $request, string $model, int $index, string $field): string
    {
        switch ($request['input_types'][$index]) {
            case 'url':
                return $validation . self::RULE_URL;

            case 'email':
                if (GeneratorUtils::checkGeneratorVariant() != GeneratorVariant::SINGLE_FORM->value) {
                    return $validation . self::RULE_EMAIL . $this->buildUniqueRule($model, $field);
                }
                return $validation;

            case 'date':
                return $validation . self::RULE_DATE;

            case 'password':
                return $validation . self::RULE_CONFIRMED . self::RULE_PASSWORD_MIN;

            case 'file':
                return $this->addFileValidations($validation, $request, $index);

            case 'text':
            case 'textarea':
                return $validation . self::RULE_STRING;

            case 'number':
            case 'range':
            case 'year':
                return $validation . self::RULE_NUMERIC;

            default:
                return $validation;
        }
    }

    /**
     * Build unique rule for email/unique fields.
     */
    protected function buildUniqueRule(string $model, string $field): string
    {
        return self::RULE_UNIQUE_OPEN . GeneratorUtils::pluralSnakeCase($model) .
            self::RULE_UNIQUE_MIDDLE . GeneratorUtils::singularSnakeCase($field) . self::RULE_UNIQUE_CLOSE;
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
        return $validation . self::RULE_IMAGE . self::RULE_IMAGE_MAX . $maxSize . self::RULE_IMAGE_CLOSE;
    }

    /**
     * Build mimes validation string.
     */
    protected function buildMimesValidation(string $validation, array $request, int $index): string
    {
        $mimes = implode(',', $request['mimes'][$index]);
        $size = $request['files_sizes'][$index];
        return $validation . "'mimes:$mimes', 'size:$size', ";
    }

    /**
     * Add validations based on column type.
     */
    protected function addColumnTypeValidations(string $validation, array $request, int $index): string
    {
        switch ($request['column_types'][$index]) {
            case 'enum':
                return $validation . $this->buildEnumValidation($request['select_options'][$index]);

            case 'boolean':
                return $validation . self::RULE_BOOLEAN;

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
        $in = self::RULE_IN_OPEN;
        $options = explode('|', $options);
        $totalOptions = count($options);

        foreach ($options as $key => $option) {
            $in .= "'" . $option . "'" . ($key + 1 != $totalOptions ? ', ' : '');
        }

        return $in . self::RULE_IN_CLOSE;
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
        return $validation . $this->buildExistsRule($table);
    }

    /**
     * Build exists rule for foreign key validation.
     */
    protected function buildExistsRule(string $table): string
    {
        return self::RULE_EXISTS_OPEN . GeneratorUtils::pluralSnakeCase($table) . self::RULE_EXISTS_CLOSE;
    }

    /**
     * Add length validations (min, max, between).
     */
    protected function addLengthValidations(string $validation, array $request, int $index): string
    {
        if ($this->shouldAddBetweenRule($request, $index)) {
            return $validation . $this->buildBetweenRule(
                $request['min_lengths'][$index],
                $request['max_lengths'][$index]
            );
        }

        $validation = $this->addMinValidation($validation, $request, $index);
        return $this->addMaxValidation($validation, $request, $index);
    }

    /**
     * Check if between rule should be added.
     */
    protected function shouldAddBetweenRule(array $request, int $index): bool
    {
        return $request['input_types'][$index] == 'range' && $request['max_lengths'][$index] >= 0;
    }

    /**
     * Build between validation rule.
     */
    protected function buildBetweenRule(int $min, int $max): string
    {
        return self::RULE_BETWEEN . $min . "," . $max . self::RULE_LENGTH_CLOSE;
    }

    /**
     * Add min length validation if needed.
     */
    protected function addMinValidation(string $validation, array $request, int $index): string
    {
        if ($request['min_lengths'][$index] && $request['input_types'][$index] !== 'range') {
            return $validation . self::RULE_MIN . $request['min_lengths'][$index] . self::RULE_LENGTH_CLOSE;
        }
        return $validation;
    }

    /**
     * Add max length validation if needed.
     */
    protected function addMaxValidation(string $validation, array $request, int $index): string
    {
        if (
            $request['max_lengths'][$index] && $request['max_lengths'][$index] >= 0 &&
            $request['input_types'][$index] !== 'range'
        ) {
            return $validation . self::RULE_MAX . $request['max_lengths'][$index] . self::RULE_LENGTH_CLOSE;
        }
        return $validation;
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
        foreach ($request['input_types'] as $key => $input) {
            if ($input == 'password' && $request['requireds'][$key] == 'yes') {
                $validations = str_replace(
                    "'" . $request['fields'][$key] . "' => ['required'",
                    "'" . $request['fields'][$key] . "' => ['nullable'",
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
        foreach ($request['input_types'] as $key => $input) {
            if (
                isset($request['file_types'][$key]) &&
                $request['file_types'][$key] == 'image' &&
                $request['requireds'][$key] == 'yes'
            ) {
                $validations = str_replace(
                    "'" . $request['fields'][$key] . "' => ['required'",
                    "'" . $request['fields'][$key] . "' => ['nullable'",
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
        foreach ($request['input_types'] as $key => $input) {
            if ($input == 'email') {
                $validations = str_replace(
                    "column: '" . str($request['fields'][$key])->snake() . "')",
                    "column: '" . str($request['fields'][$key])->snake() . "')->ignore(id: request()->segment(index: " .
                    (GeneratorUtils::isGenerateApi() ? 3 : 2) . ")), ",
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
        $imports = [
            'Rule' => str_contains($validation, "Rule::"),
            'Password' => str_contains($validation, "Password::"),
            'File' => str_contains($validation, "File::"),
        ];

        $useStatements = '';
        foreach ($imports as $class => $needed) {
            if ($needed) {
                $useStatements .= "use Illuminate\\Validation\\Rules\\{$class};\n";
            }
        }

        return str_replace(
            ['{{validationRule}}', '{{passwordRule}}', '{{fileRule}}'],
            $useStatements,
            $validation
        );
    }
}
