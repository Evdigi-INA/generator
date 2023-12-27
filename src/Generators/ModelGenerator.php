<?php

namespace EvdigiIna\Generator\Generators;

class ModelGenerator
{
    /**
     * Generate a model file.
     */
    public function generate(array $request): void
    {
        $path = GeneratorUtils::getModelLocation($request['model']);
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase($model);

        $fields = "[";
        $casts = "[";
        $relations = "";
        $totalFields = count($request['fields']);
        $dateTimeFormat = config('generator.format.datetime') ? config('generator.format.datetime') : 'Y-m-d H:i:s';
        $protectedHidden = "";

        if (in_array('password', $request['input_types'])) {
            $protectedHidden .= <<<PHP
            /**
                 * The attributes that should be hidden for serialization.
                 *
                 * @var string[]
                */
                protected \$hidden = [
            PHP;
        }

        $namespace = !$path ? "namespace App\\Models;" : "namespace App\\Models\\$path;";

        foreach ($request['fields'] as $i => $field) {
            switch ($i + 1 != $totalFields) {
                case true:
                    $fields .= "'" . str()->snake($field) . "', ";
                    break;
                default:
                    $fields .= "'" . str()->snake($field) . "']";
                    break;
            }

            if ($request['input_types'][$i] == 'password') {
                $protectedHidden .= "'" . str()->snake($field) . "', ";
            }

            switch ($request['column_types'][$i]) {
                case 'date':
                    if ($request['input_types'][$i] != 'month') {
                        $dateFormat = config('generator.format.date') ? config('generator.format.date') : 'd/m/Y';
                        $casts .= "'" . str()->snake($field) . "' => 'date:$dateFormat', ";
                    }
                    break;
                case 'time':
                    $timeFormat = config('generator.format.time') ? config('generator.format.time') : 'H:i';
                    $casts .= "'" . str()->snake($field) . "' => 'datetime:$timeFormat', ";
                    break;
                case 'year':
                    $casts .= "'" . str()->snake($field) . "' => 'integer', ";
                    break;
                case 'dateTime':
                    $casts .= "'" . str()->snake($field) . "' => 'datetime:$dateTimeFormat', ";
                    break;
                case 'float':
                    $casts .= "'" . str()->snake($field) . "' => 'float', ";
                    break;
                case 'boolean':
                    $casts .= "'" . str()->snake($field) . "' => 'boolean', ";
                    break;
                case 'double':
                    $casts .= "'" . str()->snake($field) . "' => 'double', ";
                    break;
                case 'foreignId':
                    $constrainPath = GeneratorUtils::getModelLocation($request['constrains'][$i]);
                    $constrainName = GeneratorUtils::setModelName($request['constrains'][$i]);

                    $foreign_id = isset($request['foreign_ids'][$i]) ? ", '" . $request['foreign_ids'][$i] . "'" : '';

                    if ($i > 0) {
                        $relations .= "\t";
                    }

                    /**
                     * will generate something like:
                     * \App\Models\Main\Product::class
                     *              or
                     *  \App\Models\Product::class
                     */
                    if ($constrainPath != '') {
                        $constrainPath = "\\App\\Models\\$constrainPath\\$constrainName";
                    } else {
                        $constrainPath = "\\App\\Models\\$constrainName";
                    }

                    /**
                     * will generate something like:
                     *
                     * public function product()
                     * {
                     *     return $this->belongsTo(\App\Models\Main\Product::class);
                     *                              or
                     *     return $this->belongsTo(\App\Models\Product::class);
                     * }
                     */
                    $relations .= "\n\tpublic function " . str()->snake($constrainName) . "()\n\t{\n\t\treturn \$this->belongsTo(" . $constrainPath . "::class" . $foreign_id . ");\n\t}";
                    break;
            }

            switch ($request['input_types'][$i]) {
                case 'month':
                    $castFormat = config('generator.format.month') ? config('generator.format.month') : 'm/Y';
                    $casts .= "'" . str()->snake($field) . "' => 'date:$castFormat', ";
                    break;
                case 'week':
                    $casts .= "'" . str()->snake($field) . "' => 'date:Y-\WW', ";
                    break;
            }

            // integer/bigInteger/tinyInteger/
            if (str_contains($request['column_types'][$i], 'integer')) {
                $casts .= "'" . str()->snake($field) . "' => 'integer', ";
            }

            if (in_array($request['column_types'][$i], ['string', 'text', 'char']) && $request['input_types'][$i] != 'week') {
                $casts .= "'" . str()->snake($field) . "' => 'string', ";
            }
        }

        if ($protectedHidden != "") {
            // remove "', " and then change to "'" in the of array for better code.
            // $protectedHidden  = str_replace("', ", "'", $protectedHidden);
            $protectedHidden = substr($protectedHidden, 0, -2) . "];";
        }

        // $casts .= <<<PHP
        // 'created_at' => 'datetime:$dateTimeFormat', 'updated_at' => 'datetime:$dateTimeFormat']
        // PHP;

        $casts .= "]";

        $template = str_replace(
            [
                '{{modelName}}',
                '{{fields}}',
                '{{casts}}',
                '{{relations}}',
                '{{namespace}}',
                '{{protectedHidden}}',
                '{{pluralSnakeCase}}',
            ],
            [
                $modelNameSingularPascalCase,
                $fields,
                $casts,
                $relations,
                $namespace,
                $protectedHidden,
                GeneratorUtils::pluralSnakeCase($model),
            ],
            GeneratorUtils::getTemplate('model')
        );

        if (!$path) {
            file_put_contents(app_path("/Models/$modelNameSingularPascalCase.php"), $template);
        } else {
            $fullPath = app_path("/Models/$path");
            GeneratorUtils::checkFolder($fullPath);
            file_put_contents($fullPath . "/$modelNameSingularPascalCase.php", $template);
        }
    }
}
