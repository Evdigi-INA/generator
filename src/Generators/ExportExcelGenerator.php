<?php

namespace EvdigiIna\Generator\Generators;

class ExportExcelGenerator
{
    public function generate(array $request): void
    {
        if (isset($request['generate_export']) && $request['generate_export'] == 'on') {
            $path = GeneratorUtils::getModelLocation(model: $request['model']);
            $modelPath = $path ? "$path\\" : '';
            $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase(string: $request['model']);

            $headings = '';
            $map = '';
            $relations = '';

            if (in_array(needle: 'foreignId', haystack: $request['column_types'])) {
                $relations .= 'with([';

                foreach ($request['constrains'] as $i => $c) {
                    if ($c) {
                        // remove path or '/' if exists
                        $constrainName = GeneratorUtils::setModelName(model: $request['constrains'][$i]);

                        $constrainSnakeCase = GeneratorUtils::singularSnakeCase(string: $constrainName);
                        $selectedColumns = GeneratorUtils::selectColumnAfterIdAndIdItself(table: $constrainName);

                        /**
                         * will generate something like:
                         *
                         * 'user:id,name',
                         */
                        $relations .= "'$constrainSnakeCase:$selectedColumns', ";
                    }
                }

                $relations .= '])';
            } else {
                $relations .= 'query()';
            }

            foreach ($request['fields'] as $i => $f) {
                if ($i >= 1) {
                    $headings .= "\t\t\t";
                    $map .= "\t\t\t";
                }

                /**
                 * will generate something like:
                 *
                 * $row->user?->name,
                 */
                switch ($request['column_types'][$i]) {
                    case 'foreignId':
                        $constrainModel = GeneratorUtils::setModelName(model: $request['constrains'][$i], style: 'default');

                        $map .= '$row->'.GeneratorUtils::singularSnakeCase(string: $constrainModel).'?->'.GeneratorUtils::getColumnAfterId(table: $constrainModel).',';
                        $headings .= "'".GeneratorUtils::cleanSingularUcWords(string: $constrainModel)."',";
                        break;
                    case 'time':
                        $map .= '$row->'.str(string: $f)->snake()."?->format('".config(key: 'generator.format.time', default: 'H:i')."'),";
                        $headings .= "'".GeneratorUtils::cleanSingularUcWords(string: $f)."',";
                        break;
                    case 'dateTime':
                        $map .= '$row->'.str(string: $f)->snake()."?->format('".config(key: 'generator.format.datetime', default: 'Y-m-d-H:i')."'),";
                        $headings .= "'".GeneratorUtils::cleanSingularUcWords(string: $f)."',";
                        break;
                    case 'date':
                        if ($request['input_types'][$i] == 'date') {
                            $map .= '$row->'.str(string: $f)->snake()."?->format('".config(key: 'generator.format.date', default: 'Y-m-d')."'),";
                            $headings .= "'".GeneratorUtils::cleanSingularUcWords(string: $f)."',";
                        } else {
                            $map .= '$row->'.str(string: $f)->snake()."?->format('".config(key: 'generator.format.month', default: 'Y-m')."'),";
                            $headings .= "'".GeneratorUtils::cleanSingularUcWords(string: $f)."',";
                        }
                        break;
                    case 'char':
                        if ($request['input_types'][$i] == 'week') {
                            $map .= '$row->'.str(string: $f)->snake()."?->format('Y-\WW'),";
                            $headings .= "'".GeneratorUtils::cleanSingularUcWords(string: $f)."',";
                        }
                        break;
                    default:
                        $map .= '$row->'.str(string: $f)->snake().',';
                        $headings .= "'".GeneratorUtils::cleanSingularUcWords(string: $f)."',";
                        break;
                }

                if ($i + 1 < count(value: $request['fields'])) {
                    $headings .= "\n";
                    $map .= "\n";
                }
            }

            $template = GeneratorUtils::replaceStub(stubName: 'export', replaces: [
                'modelPath' => "App\Models\\".$modelPath.$modelNameSingularPascalCase,
                'modelName' => $modelNameSingularPascalCase,
                'headings' => $headings,
                'map' => $map,
                'relations' => $relations,
                'modelNamePlural' => GeneratorUtils::pluralPascalCase(string: $request['model']),
                'dateTimeFormat' => config(key: 'generator.format.datetime', default: 'Y-m-d H:i:s'),
            ]);

            GeneratorUtils::checkFolder(path: app_path(path: 'Exports'));

            file_put_contents(filename: app_path(path: 'Exports/'.GeneratorUtils::pluralPascalCase(string: $request['model']).'Export.php'), data: $template);
        }
    }
}
