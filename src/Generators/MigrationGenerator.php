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
                switch ($request['column_types'][$i]) {
                    case 'enum':
                        /**
                         * will generate something like:
                         * $table->string('name', ['water', 'fire'])
                         */
                        $setFields .= ")";
                        break;

                    default:
                        /**
                         * will generate something like:
                         * $table->string('name', 30)
                         */
                        $setFields .= match ($request['input_types'][$i]) {
                            'range' => "')",
                            default => "', " . $request['max_lengths'][$i] . ")",
                        };
                        break;
                }
            } else {
                switch ($request['column_types'][$i]) {
                    case 'enum':
                        /**
                         * will generate something like:
                         * $table->string('name', ['water', 'fire'])
                         */
                        $setFields .= ")";
                        break;

                    default:
                        /**
                         * will generate something like:
                         * $table->string('name')
                         */
                        $setFields .= "')";
                        break;
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
                switch ($request['column_types'][$i]) {
                    case 'foreignId':
                        if ($request['on_delete_foreign'][$i] == ActionForeign::NULL->value) {
                            $setFields .= "->nullable()";
                        }

                        $setFields .= "->constrained('" . GeneratorUtils::pluralSnakeCase($constrainName) . "')";

                        $setFields .= match ($request['on_update_foreign'][$i]) {
                            ActionForeign::CASCADE->value => "->cascadeOnUpdate()",
                            ActionForeign::RESTRICT->value => "->restrictOnUpdate()",
                        };

                        $setFields .= match ($request['on_delete_foreign'][$i]) {
                            ActionForeign::CASCADE->value => "->cascadeOnDelete();\n\t\t\t",
                            ActionForeign::RESTRICT->value => "->restrictOnDelete();\n\t\t\t",
                            ActionForeign::NULL->value => "->nullOnDelete();\n\t\t\t",
                            default => ";\n\t\t\t",
                        };
                        break;

                    default:
                        $setFields .= ";\n\t\t\t";
                        break;
                }
            } else {
                switch ($request['column_types'][$i]) {
                    case 'foreignId':
                        $setFields .= "->constrained('" . GeneratorUtils::pluralSnakeCase($constrainName) . "')";

                        $setFields .= match ($request['on_update_foreign'][$i]) {
                            ActionForeign::CASCADE->value => "->cascadeOnUpdate()",
                            ActionForeign::RESTRICT->value => "->restrictOnUpdate()",
                            default => "",
                        };

                        $setFields .= match ($request['on_delete_foreign'][$i]) {
                            ActionForeign::CASCADE->value => "->cascadeOnDelete();",
                            ActionForeign::RESTRICT->value => "->restrictOnDelete();",
                            ActionForeign::NULL->value => "->nullOnDelete();",
                            default => ";",
                        };
                        break;

                    default:
                        $setFields .= ";";
                        break;
                }
            }
        }

        $template = GeneratorUtils::replaceStub(replaces: [
            'tableNamePluralLowercase' => $tableNamePluralLowercase,
            'fields' => $setFields,
        ], stubName: 'migration');

        $migrationName = date('Y') . '_' . date('m') . '_' . date('d') . '_' . date('h') . date('i') . date('s') . '_create_' . $tableNamePluralLowercase . '_table.php';

        file_put_contents(database_path("/migrations/$migrationName"), $template);
    }
}
