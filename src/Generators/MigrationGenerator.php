<?php

namespace EvdigiIna\Generator\Generators;

use EvdigiIna\Generator\Enums\ActionForeign;

class MigrationGenerator
{
    /**
     * Generate a migration file.
     */
    public function generate(array $request): void
    {
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $tableNamePluralLowercase = GeneratorUtils::pluralSnakeCase($model);

        $setFields = '';
        $totalFields = count($request['fields']);

        foreach ($request['fields'] as $i => $field) {
            /**
             * will generate something like:
             * $table->string('name
             */
            $setFields .= "\$table->" . $request['column_types'][$i] . "('" . str()->snake($field);

            /**
             * will generate something like:
             * $table->string('name
             */
            if ($request['column_types'][$i] == 'enum') {
                $options = explode('|', $request['select_options'][$i]);
                $totalOptions = count($options);

                $enum = "[";

                foreach ($options as $key => $value) {
                    if ($key + 1 != $totalOptions) {
                        $enum .= "'$value', ";
                    } else {
                        $enum .= "'$value']";
                    }
                }

                /**
                 * will generate something like:
                 * $table->string('name', ['water', 'fire']
                 */
                $setFields .= "', " . $enum;
            }

            if (isset($request['max_lengths'][$i]) && $request['max_lengths'][$i] >= 0) {
                if ($request['column_types'][$i] == 'enum') {
                    /**
                     * will generate something like:
                     * $table->string('name', ['water', 'fire'])
                     */
                    $setFields .=  ")";
                } else {
                    /**
                     * will generate something like:
                     * $table->string('name', 30)
                     */
                    switch ($request['input_types'][$i]) {
                        case 'range':
                            $setFields .= "')";
                            break;
                        default:
                            $setFields .=  "', " . $request['max_lengths'][$i] . ")";
                            break;
                    }
                }
            } else {
                if ($request['column_types'][$i] == 'enum') {
                    /**
                     * will generate something like:
                     * $table->string('name', ['water', 'fire'])
                     */
                    $setFields .=  ")";
                } else {
                    /**
                     * will generate something like:
                     * $table->string('name')
                     */
                    $setFields .= "')";
                }
            }

            if ($request['requireds'][$i] != 'yes') {
                /**
                 * will generate something like:
                 * $table->string('name', 30)->nullable() or $table->string('name')->nullable()
                 */
                $setFields .= "->nullable()";
            }

            if ($request['default_values'][$i]) {
                /**
                 * will generate something like:
                 * $table->string('name', 30)->nullable() or $table->string('name')->nullable()
                 */

                $defaultValue = "'" . $request['default_values'][$i] . "'";

                if ($request['input_types'][$i] == 'month') {
                    $defaultValue = "\Carbon\Carbon::createFromFormat('Y-m', '" . $request['default_values'][$i] . "')";
                }

                $setFields .= "->default($defaultValue)";
            }

            if ($request['input_types'][$i] === 'email') {
                /**
                 * will generate something like:
                 * ->unique()
                 */
                $setFields .= "->unique()";
            }

            $constrainName = '';
            if ($request['column_types'][$i] == 'foreignId') {
                // remove path or '/' if exists
                $constrainName = GeneratorUtils::setModelName($request['constrains'][$i]);
            }

            if ($i + 1 != $totalFields) {
                if ($request['column_types'][$i] == 'foreignId') {
                    if ($request['on_delete_foreign'][$i] == ActionForeign::NULL->value) {
                        $setFields .= "->nullable()";
                    }

                    $setFields .= "->constrained('" . GeneratorUtils::pluralSnakeCase($constrainName) . "')";

                    switch($request['on_update_foreign'][$i]) {
                        case ActionForeign::CASCADE->value:
                            $setFields .= "->cascadeOnUpdate()";
                            break;
                        case ActionForeign::RESTRICT->value:
                            $setFields .= "->restrictOnUpdate()";
                            break;
                    }

                    switch($request['on_delete_foreign'][$i]) {
                        case ActionForeign::CASCADE->value:
                            $setFields .= "->cascadeOnDelete();\n\t\t\t";
                            break;
                        case ActionForeign::RESTRICT->value:
                            $setFields .= "->restrictOnDelete();\n\t\t\t";
                            break;
                        case ActionForeign::NULL->value:
                            $setFields .= "->nullOnDelete();\n\t\t\t";
                            break;
                        default:
                            $setFields .= ";\n\t\t\t";
                            break;
                    }
                } else {
                    $setFields .= ";\n\t\t\t";
                }
            } else {
                if ($request['column_types'][$i] == 'foreignId') {
                    $setFields .= "->constrained('" . GeneratorUtils::pluralSnakeCase($constrainName) . "')";

                    switch($request['on_update_foreign'][$i]) {
                        case ActionForeign::CASCADE->value:
                            $setFields .= "->cascadeOnUpdate()";
                            break;
                        case ActionForeign::RESTRICT->value:
                            $setFields .= "->restrictOnUpdate()";
                            break;
                    }

                    switch($request['on_delete_foreign'][$i]) {
                        case ActionForeign::CASCADE->value:
                            $setFields .= "->cascadeOnDelete();";
                            break;
                        case ActionForeign::RESTRICT->value:
                            $setFields .= "->restrictOnDelete();";
                            break;
                        case ActionForeign::NULL->value:
                            $setFields .= "->nullOnDelete();";
                            break;
                        default:
                            $setFields .= ";";
                            break;
                    }
                } else {
                    $setFields .= ";";
                }
            }
        }

        $template = str_replace(
            [
                '{{tableNamePluralLowercase}}',
                '{{fields}}'
            ],
            [
                $tableNamePluralLowercase,
                $setFields
            ],
            GeneratorUtils::getTemplate('migration')
        );

        $migrationName = date('Y') . '_' . date('m') . '_' . date('d')  . '_' . date('h') .  date('i') . date('s') . '_create_' . $tableNamePluralLowercase . '_table.php';

        file_put_contents(database_path("/migrations/$migrationName"), $template);
    }
}
