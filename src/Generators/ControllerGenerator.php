<?php

namespace EvdigiIna\Generator\Generators;

use EvdigiIna\Generator\Enums\GeneratorVariant;

class ControllerGenerator
{
    /**
     * Generate a controller file.
     */
    public function generate(array $request): void
    {
        $model = GeneratorUtils::setModelName(model: $request['model'], style: 'default');
        $path = GeneratorUtils::getModelLocation(model: $request['model']);

        $modelNameSingularCamelCase = GeneratorUtils::singularCamelCase(string: $model);
        $modelNamePluralCamelCase = GeneratorUtils::pluralCamelCase(string: $model);
        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase(string: $model);
        $modelNameSpaceLowercase = GeneratorUtils::cleanSingularLowerCase(string: $model);
        $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase(string: $model);
        $modelNamePluralPascalCase = GeneratorUtils::pluralPascalCase(string: $model);
        $modelNameCleanSingular = GeneratorUtils::cleanSingularLowerCase(string: $model);
        $modelNameCleanPlural = GeneratorUtils::cleanPluralLowerCase(string: $model);

        $query = "$modelNameSingularPascalCase::query()";

        switch ($path) {
            case '':
                $namespace = "namespace App\Http\Controllers;\n";

                if (GeneratorUtils::isGenerateApi()) {
                    $namespace = "namespace App\Http\Controllers\Api;\n\nuse App\Http\Controllers\Controller;";
                }

                /**
                 * will generate something like:
                 *
                 * use App\Http\Requests\{StoreProductRequest, UpdateProductRequest};
                 */
                $requestPath = match (GeneratorUtils::checkGeneratorVariant()) {
                    GeneratorVariant::SINGLE_FORM->value => "App\Http\Requests\\$modelNamePluralPascalCase\Store".$modelNameSingularPascalCase.'Request',
                    default => "App\Http\Requests\\$modelNamePluralPascalCase\{Store".$modelNameSingularPascalCase.'Request, Update'.$modelNameSingularPascalCase.'Request}'
                };

                break;
            default:
                /**
                 * Will generate something like:
                 *
                 * namespace App\Http\Controllers\Inventory;
                 *
                 * use App\Http\Controllers\Controller;
                 */
                $namespace = "namespace App\Http\Controllers\\$path;\n\nuse App\Http\Controllers\Controller;";

                if (GeneratorUtils::isGenerateApi()) {
                    $namespace = "namespace App\Http\Controllers\Api\\$path;\n\nuse App\Http\Controllers\Controller;";
                }

                /**
                 * Will generate something like:
                 *
                 * use App\Http\Requests\Inventory\{StoreProductRequest, UpdateProductRequest};
                 */
                $requestPath = match (GeneratorUtils::checkGeneratorVariant()) {
                    GeneratorVariant::SINGLE_FORM->value => "App\Http\Requests\\$path\\$modelNamePluralPascalCase\Store".$modelNameSingularPascalCase.'Request',
                    default => "App\Http\Requests\\$path\\$modelNamePluralPascalCase\{Store".$modelNameSingularPascalCase.'Request, Update'.$modelNameSingularPascalCase.'Request}'
                };

                break;
        }

        $relations = '';
        $addColumns = '';

        if (
            in_array(needle: 'text', haystack: $request['column_types']) ||
            in_array(needle: 'longText', haystack: $request['column_types'])
        ) {
            $limitText = config(key: 'generator.format.limit_text') ?? 200;

            /**
             * will generate something like:
             *
             *  ->addColumn(name: 'address', content: fn($row) => str(string: $row->address)->limit(100))
             */
            foreach ($request['column_types'] as $i => $type) {
                if (in_array(needle: $type, haystack: ['text', 'longText'])) {
                    $addColumns .= "->addColumn(name: '".str(string: $request['fields'][$i])->snake()."', content: fn(\$row): ?string => str(string: \$row->".str(string: $request['fields'][$i])->snake().")->limit($limitText))\n\t\t\t\t";
                }
            }
        }

        // load the relations for create, show, and edit
        if (in_array(needle: 'foreignId', haystack: $request['column_types'])) {
            if (GeneratorUtils::isGenerateApi()) {
                $relations .= 'with(relations: [';

                $countForeignId = count(value: array_keys(array: $request['column_types'], filter_value: 'foreignId'));

                foreach ($request['constrains'] as $i => $constrain) {
                    if ($constrain != null) {
                        // remove path or '/' if exists
                        $constrainName = GeneratorUtils::setModelName(model: $request['constrains'][$i]);

                        $constrainSnakeCase = GeneratorUtils::singularSnakeCase(string: $constrainName);
                        $selectedColumns = GeneratorUtils::selectColumnAfterIdAndIdItself(table: $constrainName);

                        $relations .= "'$constrainSnakeCase:$selectedColumns', ";

                        if ($countForeignId + 1 < $i) {
                            $query .= "'$constrainSnakeCase:$selectedColumns', ";
                        } else {
                            $query .= "'$constrainSnakeCase:$selectedColumns'";
                        }
                    }
                }

                $relations .= '])->';
                $relations = str_replace(search: "', ])->", replace: "'])->", subject: $relations);
            } else {
                $relations .= '$'.$modelNameSingularCamelCase.'->load(relations: [';

                $countForeignId = count(value: array_keys(array: $request['column_types'], filter_value: 'foreignId'));

                $query = "$modelNameSingularPascalCase::with(relations: [";

                foreach ($request['constrains'] as $i => $constrain) {
                    if ($constrain != null) {
                        // remove path or '/' if exists
                        $constrainName = GeneratorUtils::setModelName(model: $request['constrains'][$i]);

                        $constrainSnakeCase = GeneratorUtils::singularSnakeCase(string: $constrainName);
                        $selectedColumns = GeneratorUtils::selectColumnAfterIdAndIdItself(table: $constrainName);
                        $columnAfterId = GeneratorUtils::getColumnAfterId(table: $constrainName);

                        if ($countForeignId + 1 < $i) {
                            $relations .= "'$constrainSnakeCase:$selectedColumns', ";
                            $query .= "'$constrainSnakeCase:$selectedColumns', ";
                        } else {
                            $relations .= "'$constrainSnakeCase:$selectedColumns'";
                            $query .= "'$constrainSnakeCase:$selectedColumns'";
                        }

                        /**
                         * Will generate something like:
                         *
                         *  ->addColumn(name: 'user', content: fn($row) => $row?->user?->name ?? '')
                         */
                        $addColumns .= "->addColumn(name: '$constrainSnakeCase', content: fn(\$row): ?string => \$row?->".$constrainSnakeCase."?->$columnAfterId ?? '')\n\t\t\t\t";
                    }
                }

                $query .= '])';
                $relations .= "]);\n\n\t\t";

                $query = str_replace(search: "''", replace: "', '", subject: $query);
                $relations = str_replace(search: "''", replace: "', '", subject: $relations);
            }
        }

        /**
         * will generate something like:
         *
         * User::create(attributes: $request->validated());
         * $user->update(attributes: $request->validated());
         */
        $insertDataAction = match (GeneratorUtils::isGenerateApi()) {
            true => "$$modelNameSingularCamelCase = $modelNameSingularPascalCase::create(attributes: \$request->validated());",
            false => "$modelNameSingularPascalCase::create(attributes: \$request->validated());",
        };

        $updateDataAction = "\${$modelNameSingularCamelCase}->update(attributes: \$request->validated());";
        $requestValidatedAttr = '';

        if (in_array(needle: 'password', haystack: $request['input_types']) || in_array(needle: 'month', haystack: $request['input_types'])) {
            /**
             * * will generate something like:
             *
             *  User::create($validated);
             *  $user->update($validated);
             */
            $insertDataAction = "$modelNameSingularPascalCase::create(attributes: \$validated);";
            $updateDataAction = "\${$modelNameSingularCamelCase}->update(attributes: \$validated);";
            $requestValidatedAttr = "\$validated = \$request->validated();\n";
        }

        $passwordFieldStore = '';
        $passwordFieldUpdate = '';
        if (in_array(needle: 'password', haystack: $request['input_types'])) {
            $passwordFieldStore .= $requestValidatedAttr;
            $passwordFieldUpdate .= $requestValidatedAttr;

            foreach ($request['input_types'] as $i => $input) {
                info('pass', [
                    'input' => $input,
                    'i' => $i,
                    'field' => $request['fields'][$i],
                ]);

                info('snake', [
                    'field' => str()->snake($request['fields'][$i]),
                ]);
                if ($input === 'password') {
                    /**
                     * will generate something like:
                     *
                     * $validated['password'] = bcrypt($request->password);
                     */
                    $passwordFieldStore .= "\t\t\$validated['".str(string: $request['fields'][$i])->snake()."'] = bcrypt(\$request->".str(string: $request['fields'][$i])->snake().");\n";

                    $passwordFieldUpdate .= '
        if (!$request->'.str()->snake($request['fields'][$i]).") {
            unset(\$validated['".str()->snake($request['fields'][$i])."']);
        } else {
            \$validated['".str()->snake($request['fields'][$i])."'] = bcrypt(\$request->".str()->snake($request['fields'][$i]).");
        }\n";
                }
            }
        }

        /**
         * Generate code for insert input type month with relations: datatype date.
         * by default will get an error, cause invalid format.
         */
        $inputMonths = '';
        if (in_array(needle: 'month', haystack: $request['input_types'])) {
            if (! in_array(needle: 'password', haystack: $request['input_types'])) {
                /**
                 * don't concat string if any input type password, cause already concat ahead.
                 */
                $inputMonths .= $requestValidatedAttr;
            }

            foreach ($request['input_types'] as $i => $input) {
                if ($input == 'month') {
                    /**
                     * will generate something like:
                     *
                     * $validated['month'] = $request->month ? \Carbon\Carbon::createFromFormat('Y-m', $request->month)->toDateTimeString() : null;
                     */
                    $inputMonths .= "\t\t\$validated['".str()->snake($request['fields'][$i])."'] = \$request->".str()->snake($request['fields'][$i])." ? \Carbon\Carbon::createFromFormat('Y-m', \$request->".str()->snake($request['fields'][$i]).")->toDateTimeString() : null;\n";
                }
            }
        }

        /**
         * Generate a codes for upload file.
         */
        switch (in_array(needle: 'file', haystack: $request['input_types'])) {
            case true:
                $storeCode = '';
                $updateCode = '';
                $uploadPaths = '';
                $assignImageDelete = '';
                $deleteImageCodes = '';

                /**
                 *    if (!$generator->image) {
                 *     $generator->image = 'https://via.placeholder.com/350?text=No+Image+Avaiable';
                 *    } else {
                 *      $generator->image =  asset('/uploads/images/' . $generator->image);
                 *    }
                 *
                 *    return $generator;
                 * })
                 */
                foreach ($request['input_types'] as $i => $input) {
                    if ($input == 'file') {
                        $uploadPaths .= 'public string $'.GeneratorUtils::singularCamelCase(string: $request['fields'][$i])."Path = '".GeneratorUtils::pluralKebabCase(string: $request['fields'][$i])."', ";

                        //  Generated code: $image = $generator->image;
                        $assignImageDelete .= '$'.GeneratorUtils::singularCamelCase(string: $request['fields'][$i]).' = $'.GeneratorUtils::singularCamelCase(string: $model).'->'.str(string: $request['fields'][$i])->snake().";\n\t\t\t";

                        //  Generated code: $this->imageService->delete("$this->imagePath$image", disk: $this->disk);
                        $deleteImageCodes .= GeneratorUtils::replaceStub(replaces: [
                            'fieldCamelCase' => GeneratorUtils::singularCamelCase(string: $request['fields'][$i]),
                        ], stubName: 'controllers/upload-files/delete')."\n\t\t\t";

                        $storeCode .= $this->generateUploadImageCode(
                            field: $request['fields'][$i],
                            path: 'store',
                            model: $modelNameSingularCamelCase
                        );

                        $updateCode .= $this->generateUploadImageCode(
                            field: $request['fields'][$i],
                            path: 'update',
                            model: $modelNameSingularCamelCase
                        );
                    }
                }

                /**
                 * Remove $validated = $request->validated(); because is already exist in template (.stub)
                 */
                $passwordFieldStore = str_replace(search: '$validated = $request->validated();', replace: '', subject: $passwordFieldStore);
                $passwordFieldUpdate = str_replace(search: '$validated = $request->validated();', replace: '', subject: $passwordFieldUpdate);

                $inputMonths = str_replace(search: '$validated = $request->validated();', replace: '', subject: $inputMonths);
                $updateDataAction = "\${$modelNameSingularCamelCase}->update(attributes: \$validated);";

                if (GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value) {
                    /**
                     *  if($product) {
                     *      $product->update($validated);
                     *  } else {
                     *      Product::create($validated);
                     *  }
                     */
                    $singleFormUpdateDataAction = "$modelNameSingularPascalCase::updateOrCreate(attributes: ['id' => $".$modelNameSingularCamelCase.'?->id], values: '.(str_contains(haystack: $updateDataAction, needle: '$request->validated()') ? '$request->validated()' : '$validated').');';

                    $updateDataAction = $singleFormUpdateDataAction;
                }

                /**
                 * controller with relations: upload file code
                 */
                $template = GeneratorUtils::replaceStub(
                    replaces: [
                        'modelNameSingularPascalCase' => $modelNameSingularPascalCase,
                        'modelNameSingularCamelCase' => $modelNameSingularCamelCase,
                        'modelNamePluralCamelCase' => $modelNamePluralCamelCase,
                        'modelNamePluralKebabCase' => $modelNamePluralKebabCase,
                        'modelNameSpaceLowercase' => $modelNameSpaceLowercase,
                        'storeCode' => $storeCode,
                        'updateCode' => $updateCode,
                        'loadRelation' => $relations,
                        'addColumns' => $addColumns,
                        'query' => $query,
                        'namespace' => $namespace,
                        'requestPath' => $requestPath,
                        'modelPath' => $path != '' ? "App\Models\\$path\\$modelNameSingularPascalCase" : "App\Models\\$modelNameSingularPascalCase",
                        'viewPath' => $path != '' ? str_replace(search: '\\', replace: '.', subject: strtolower(string: $path)).'.' : '',
                        'passwordFieldStore' => $passwordFieldStore,
                        'passwordFieldUpdate' => $passwordFieldUpdate,
                        'updateDataAction' => $updateDataAction,
                        'inputMonths' => $inputMonths,
                        'resourceApiPath' => $path != '' ? "App\Http\Resources\\$path\\$modelNamePluralPascalCase" : "App\Http\Resources\\$modelNamePluralPascalCase",
                        'modelNameCleanSingular' => $modelNameCleanSingular,
                        'modelNameCleanPlural' => $modelNameCleanPlural,
                        'relations' => $relations,
                        'uploadPaths' => $uploadPaths,
                        'assignImageDelete' => $assignImageDelete,
                        'deleteImageCodes' => $deleteImageCodes,
                        'middlewareName' => GeneratorUtils::isGenerateApi() ? "'auth:sanctum'," : "'auth',",
                        'useExportNamespace' => $this->generateExportNamespace(request: $request),
                        'exportFunction' => $this->generateExportFunction(request: $request),
                        'disk' => config(key: 'generator.image.disk', default: 'storage.local'),
                    ],
                    stubName: GeneratorUtils::getControllerStubByGeneratorVariant(withUploadFile: true)
                );
                break;
            default:
                if (GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value) {
                    /**
                     *  if($product) {
                     *      $product->update($validated);
                     *  } else {
                     *      Product::create($validated);
                     *  }
                     *
                     * Product::updateOrCreate(['id' => $product?->id], $validated);
                     */
                    $singleFormUpdateDataAction = "$modelNameSingularPascalCase::updateOrCreate(attributes: ['id' => $".$modelNameSingularCamelCase.'?->id], values: '.(str_contains(haystack: $updateDataAction, needle: '$request->validated()') ? '$request->validated()' : '$validated').');';

                    $updateDataAction = $singleFormUpdateDataAction;
                }

                /**
                 * default controller
                 */
                $template = GeneratorUtils::replaceStub(replaces: [
                    'modelNameSingularPascalCase' => $modelNameSingularPascalCase,
                    'modelNameSingularCamelCase' => $modelNameSingularCamelCase,
                    'modelNamePluralCamelCase' => $modelNamePluralCamelCase,
                    'modelNamePluralKebabCase' => $modelNamePluralKebabCase,
                    'modelNameSpaceLowercase' => $modelNameSpaceLowercase,
                    'loadRelation' => $relations,
                    'addColumns' => $addColumns,
                    'query' => $query,
                    'namespace' => $namespace,
                    'requestPath' => $requestPath,
                    'modelPath' => $path != '' ? "App\Models\\$path\\$modelNameSingularPascalCase" : "App\Models\\$modelNameSingularPascalCase",
                    'viewPath' => $path != '' ? str_replace(search: '\\', replace: '.', subject: strtolower($path)).'.' : '',
                    'passwordFieldStore' => $passwordFieldStore,
                    'passwordFieldUpdate' => $passwordFieldUpdate,
                    'insertDataAction' => $insertDataAction,
                    'updateDataAction' => $updateDataAction,
                    'inputMonths' => $inputMonths,
                    'resourceApiPath' => $path != '' ? "App\Http\Resources\\$path\\$modelNamePluralPascalCase" : "App\Http\Resources\\$modelNamePluralPascalCase",
                    'modelNameCleanSingular' => $modelNameCleanSingular,
                    'modelNameCleanPlural' => $modelNameCleanPlural,
                    'relations' => $relations,
                    'publicOrStorage' => config(key: 'generator.image.disk', default: 'storage'),
                    'middlewareName' => GeneratorUtils::isGenerateApi() ? "'auth:sanctum'," : "'auth',",
                    'exportFunction' => $this->generateExportFunction(request: $request),
                    'useExportNamespace' => $this->generateExportNamespace(request: $request),
                ], stubName: GeneratorUtils::getControllerStubByGeneratorVariant());
                break;
        }

        if (GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value) {
            $template = str_replace(search: 'created successfully', replace: 'updated successfully', subject: $template);
        }

        /**
         * Create a controller file.
         */
        if (! $path) {
            match (GeneratorUtils::isGenerateApi()) {
                true => GeneratorUtils::checkFolder(path: app_path(path: '/Http/Controllers/Api')),
                default => GeneratorUtils::checkFolder(path: app_path(path: '/Http/Controllers')),
            };

            $controllerPath = GeneratorUtils::isGenerateApi() ? "/Api/{$modelNameSingularPascalCase}Controller.php" : "/{$modelNameSingularPascalCase}Controller.php";

            file_put_contents(filename: app_path(path: "/Http/Controllers$controllerPath"), data: $template);
        } else {
            $fullPath = GeneratorUtils::isGenerateApi() ? app_path(path: "/Http/Controllers/Api/$path/") : app_path(path: "/Http/Controllers/$path/");
            GeneratorUtils::checkFolder(path: $fullPath);

            file_put_contents(filename: "$fullPath{$modelNameSingularPascalCase}Controller.php", data: $template);
        }
    }

    /**
     * Generate an upload file code.
     */
    protected function generateUploadImageCode(string $field, string $path, ?string $model): string
    {
        $replaces = [
            'fieldSnakeCase' => str()->snake($field),
            'fieldSingularCamelCase' => GeneratorUtils::singularCamelCase(string: $field),
            'defaultImage' => '$'.GeneratorUtils::singularCamelCase(string: $model).'?->'.str(string: $field)->snake(),
            'fieldCamelCase' => GeneratorUtils::singularCamelCase(string: $field),
            'disk' => config(key: 'generator.image.disk'),
        ];

        if ($model) {
            $replaces['modelNameSingularCamelCase'] = $model;
        }

        return GeneratorUtils::replaceStub(
            replaces: $replaces,
            stubName: "controllers/upload-files/$path"
        );
    }

    /**
     * Generate a cast image code.
     */
    public function generateCastImageCode(string $field, string $path, string $model): string
    {
        $replaces = [
            'modelNamePluralCamelCase' => GeneratorUtils::pluralCamelCase(string: $model),
            'modelNameSingularCamelCase' => GeneratorUtils::singularCamelCase(string: $model),
            'field' => $field,
            'defaultImage' => config(key: 'generator.image.default', default: 'https://via.placeholder.com/350?text=No+Image+Avaiable'),
            'castImage' => GeneratorUtils::setDiskCodeForCastImage(model: $model, field: $field),
            'fieldPluralKebabCase' => GeneratorUtils::pluralKebabCase(string: $field),
        ];

        return GeneratorUtils::replaceStub(
            replaces: $replaces,
            stubName: "/controllers/upload-files/cast-image-$path"
        );
    }

    /**
     * Generate an export function.
     */
    public function generateExportFunction(array $request): string
    {
        if (isset($request['generate_export']) && $request['generate_export'] == 'on') {
            $template = GeneratorUtils::replaceStub(replaces: [
                'modelPluralPascalCase' => GeneratorUtils::pluralPascalCase(string: $request['model']),
                'modelPluralKebabCase' => GeneratorUtils::pluralKebabCase(string: $request['model']),
            ], stubName: 'controllers/export-function');

            return $template;
        }

        return '';
    }

    public function generateExportNamespace(array $request): string
    {
        if (isset($request['generate_export']) && $request['generate_export'] == 'on') {
            return "\nuse App\Exports\\".GeneratorUtils::pluralPascalCase(string: $request['model'])."Export;\nuse Symfony\Component\HttpFoundation\BinaryFileResponse;";
        }

        return '';
    }
}
