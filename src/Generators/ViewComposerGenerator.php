<?php

namespace EvdigiIna\Generator\Generators;

use Illuminate\Support\Facades\Schema;

class ViewComposerGenerator
{
    /**
     * Generate view composer on viewServiceProvider, if any belongsTo relation.
     */
    public function generate(array $request): void
    {
        if (in_array(needle: 'foreignId', haystack: $request['column_types'], strict: true)) {
            $template = "";

            $model = GeneratorUtils::setModelName(model: $request['model'], style: 'default');
            $viewPath = GeneratorUtils::getModelLocation(model: $request['model']);

            foreach ($request['column_types'] as $i => $dataType) {
                if ($dataType == 'foreignId') {
                    // remove '/' or sub folders
                    $constrainModel = GeneratorUtils::setModelName(model: $request['constrains'][$i]);

                    $relatedModelPath = GeneratorUtils::getModelLocation(model: $request['constrains'][$i]);
                    $table = GeneratorUtils::pluralSnakeCase(string: $constrainModel);

                    if ($relatedModelPath != '') {
                        $relatedModelPath = "\App\Models\\$relatedModelPath\\$constrainModel";
                    } else {
                        $relatedModelPath = "\App\Models\\" . GeneratorUtils::singularPascalCase(string: $constrainModel);
                    }

                    $allColumns = Schema::getColumnListing($table);

                    if (sizeof(value: $allColumns) > 0) {
                        $fieldsSelect = "'id', '$allColumns[1]'";
                    } else {
                        $fieldsSelect = "'id'";
                    }

                    if ($i > 1)
                        $template .= "\t\t";

                    $template .= GeneratorUtils::replaceStub(
                        replaces: [
                            'modelNamePluralKebabCase' => GeneratorUtils::pluralKebabCase(string: $model),
                            'constrainsPluralCamelCase' => GeneratorUtils::pluralCamelCase(string: $constrainModel),
                            'constrainsSingularPascalCase' => GeneratorUtils::singularPascalCase(string: $constrainModel),
                            'fieldsSelect' => $fieldsSelect,
                            'relatedModelPath' => $relatedModelPath,
                            'viewPath' => $viewPath != '' ? str_replace(search: '\\', replace: '.', subject: strtolower(string: $viewPath)) . "." : '',
                        ],
                        stubName: 'view-composer'
                    );
                }
            }
            $path = app_path(path: 'Providers/ViewComposerServiceProvider.php');

            $viewProviderTemplate = substr(string: file_get_contents(filename: $path), offset: 0, length: -6) . "\n\n\t\t" . $template . "\t}\n}";

            file_put_contents(filename: $path, data: $viewProviderTemplate);
        }
    }
}
