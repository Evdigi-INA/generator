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
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);

        $modelNameSingularCamelCase = GeneratorUtils::singularCamelCase($model);
        $modelNamePluralCamelCase = GeneratorUtils::pluralCamelCase($model);
        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase($model);
        $modelNameSpaceLowercase = GeneratorUtils::cleanSingularLowerCase($model);
        $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase($model);
        $modelNamePluralPascalCase = GeneratorUtils::pluralPascalCase($model);
        $modelNameCleanSingular = GeneratorUtils::cleanSingularLowerCase($model);
        $modelNameCleanPlural = GeneratorUtils::cleanPluralLowerCase($model);

        $query = "$modelNameSingularPascalCase::query()";

        switch ($path) {
            case '':
                $namespace = "namespace App\Http\Controllers;\n";

                if (GeneratorUtils::isGenerateApi())
                    $namespace = "namespace App\Http\Controllers\Api;\n\nuse App\Http\Controllers\Controller;";

                /**
                 * will generate something like:
                 *
                 * use App\Http\Requests\{StoreProductRequest, UpdateProductRequest};
                 */
                $requestPath = match (GeneratorUtils::checkGeneratorVariant()) {
                    GeneratorVariant::SINGLE_FORM->value => "App\Http\Requests\\$modelNamePluralPascalCase\Store" . $modelNameSingularPascalCase . "Request",
                    default => "App\Http\Requests\\$modelNamePluralPascalCase\{Store" . $modelNameSingularPascalCase . "Request, Update" . $modelNameSingularPascalCase . "Request}"
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

                if (GeneratorUtils::isGenerateApi())
                    $namespace = "namespace App\Http\Controllers\Api\\$path;\n\nuse App\Http\Controllers\Controller;";

                /**
                 * Will generate something like:
                 *
                 * use App\Http\Requests\Inventory\{StoreProductRequest, UpdateProductRequest};
                 */
                $requestPath = match (GeneratorUtils::checkGeneratorVariant()) {
                    GeneratorVariant::SINGLE_FORM->value => "App\Http\Requests\\$path\\$modelNamePluralPascalCase\Store" . $modelNameSingularPascalCase . "Request",
                    default => "App\Http\Requests\\$path\\$modelNamePluralPascalCase\{Store" . $modelNameSingularPascalCase . "Request, Update" . $modelNameSingularPascalCase . "Request}"
                };

                break;
        }

        $relations = "";
        $addColumns = "";

        if (
            in_array('text', $request['column_types']) ||
            in_array('longText', $request['column_types'])
        ) {
            $limitText = config('generator.format.limit_text') ? config('generator.format.limit_text') : 200;

            /**
             * will generate something like:
             *
             * ->addColumn('role', function ($row) {
             *       return str($row->body)->limit($limitText);
             *  })
             */
            foreach ($request['column_types'] as $i => $type) {
                if (in_array($type, ['text', 'longText'])) {
                    $addColumns .= "->addColumn('" . str($request['fields'][$i])->snake() . "', function(\$row) {
                        return str(\$row->" . str($request['fields'][$i])->snake() . ")->limit($limitText);
                    })\n\t\t\t\t";
                }
            }
        }

        // load the relations for create, show, and edit
        if (in_array('foreignId', $request['column_types'])) {
            if (GeneratorUtils::isGenerateApi()) {
                $relations .= "with([";

                $countForeignId = count(array_keys($request['column_types'], 'foreignId'));

                foreach ($request['constrains'] as $i => $constrain) {
                    if ($constrain != null) {
                        // remove path or '/' if exists
                        $constrainName = GeneratorUtils::setModelName($request['constrains'][$i]);

                        $constrainSnakeCase = GeneratorUtils::singularSnakeCase($constrainName);
                        $selectedColumns = GeneratorUtils::selectColumnAfterIdAndIdItself($constrainName);

                        $relations .= "'$constrainSnakeCase:$selectedColumns', ";

                        if ($countForeignId + 1 < $i) {
                            // $relations .= "'$constrainSnakeCase:$selectedColumns', ";
                            $query .= "'$constrainSnakeCase:$selectedColumns', ";
                        } else {
                            // $relations .= "'$constrainSnakeCase:$selectedColumns'";
                            $query .= "'$constrainSnakeCase:$selectedColumns'";
                        }
                    }
                }

                $relations .= "])->";
                $relations = str_replace("', ])->", "'])->", $relations);
            } else {
                $relations .= "$" . $modelNameSingularCamelCase . "->load([";

                $countForeignId = count(array_keys($request['column_types'], 'foreignId'));

                $query = "$modelNameSingularPascalCase::with([";

                foreach ($request['constrains'] as $i => $constrain) {
                    if ($constrain != null) {
                        // remove path or '/' if exists
                        $constrainName = GeneratorUtils::setModelName($request['constrains'][$i]);

                        $constrainSnakeCase = GeneratorUtils::singularSnakeCase($constrainName);
                        $selectedColumns = GeneratorUtils::selectColumnAfterIdAndIdItself($constrainName);
                        $columnAfterId = GeneratorUtils::getColumnAfterId($constrainName);

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
                         * ->addColumn('category', function($row){
                         *     return $row?->category?->name ?? '';
                         * })
                         */
                        $addColumns .= "->addColumn('$constrainSnakeCase', function (\$row) {
                    return \$row?->" . $constrainSnakeCase . "?->$columnAfterId ?? '';
                })";
                    }
                }

                $query .= "])";
                $relations .= "]);\n\n\t\t";

                $query = str_replace(search: "''", replace: "', '", subject: $query);
                $relations = str_replace(search: "''", replace: "', '", subject: $relations);
            }
        }

        /**
         * will generate something like:
         *
         * User::create($request->validated());
         * $user->update($request->validated());
         */

        if (GeneratorUtils::isGenerateApi()) {
            // remove unused variable
            // $user = User::create($request->validated());
            $insertDataAction = "$$modelNameSingularCamelCase = $modelNameSingularPascalCase::create(\$request->validated());";
        } else {
            $insertDataAction = "$modelNameSingularPascalCase::create(\$request->validated());";
        }

        $updateDataAction = "\$" . $modelNameSingularCamelCase . "->update(\$request->validated());";
        $requestValidatedAttr = "";

        if (in_array(needle: 'password', haystack: $request['input_types']) || in_array(needle: 'month', haystack: $request['input_types'])) {
            /**
             * * will generate something like:
             *
             *  User::create($validated);
             *  $user->update($validated);
             */
            $insertDataAction = "$modelNameSingularPascalCase::create(\$validated);";
            $updateDataAction = "\$" . $modelNameSingularCamelCase . "->update(\$validated);";
            $requestValidatedAttr = "\$validated = \$request->validated();\n";
        }

        $passwordFieldStore = "";
        $passwordFieldUpdate = "";
        if (in_array('password', $request['input_types'])) {
            $passwordFieldStore .= $requestValidatedAttr;
            $passwordFieldUpdate .= $requestValidatedAttr;

            foreach ($request['input_types'] as $i => $input) {
                if ($input === 'password') {
                    /**
                     * will generate something like:
                     *
                     * $validated['password'] = bcrypt($request->password);
                     */
                    $passwordFieldStore .= "\t\t\$validated['" . str()->snake($request['fields'][$i]) . "'] = bcrypt(\$request->" . str()->snake($request['fields'][$i]) . ");\n";

                    $passwordFieldUpdate .= "
        if (!\$request->" . str()->snake($request['fields'][$i]) . ") {
            unset(\$validated['" . str()->snake($request['fields'][$i]) . "']);
        } else {
            \$validated['" . str()->snake($request['fields'][$i]) . "'] = bcrypt(\$request->" . str()->snake($request['fields'][$i]) . ");
        }\n";
                }
            }
        }

        /**
         * Generate code for insert input type month with datatype date.
         * by default will get an error, cause invalid format.
         */
        $inputMonths = "";
        if (in_array(needle: 'month', haystack: $request['input_types'])) {
            if (!in_array(needle: 'password', haystack: $request['input_types'])) {
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
                    $inputMonths .= "\t\t\$validated['" . str()->snake($request['fields'][$i]) . "'] = \$request->" . str()->snake($request['fields'][$i]) . " ? \Carbon\Carbon::createFromFormat('Y-m', \$request->" . str()->snake($request['fields'][$i]) . ")->toDateTimeString() : null;\n";
                }
            }
        }

        /**
         * Generate a codes for upload file.
         */
        switch (in_array(needle: 'file', haystack: $request['input_types'])) {
            case true:
                $indexCode = "";
                $storeCode = "";
                $updateCode = "";
                $deleteCode = "";

                $castImageFunc = "";
                $castImageIndex = "";
                $uploadPaths = "";
                $assignUploadPaths = "";
                $assignImageDelete = "";
                $deleteImageCodes = "";

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
                $castImageIndex .= "\n\t\t$" . $modelNamePluralCamelCase . "->through(function ($" . $modelNameSingularCamelCase . ") {\n\t\t\t\$this->castImages($" . $modelNameSingularCamelCase . ");\n\t\t\treturn $" . $modelNameSingularCamelCase . ";\n\t\t});\n";

                // $castImageDataTable = "";

                foreach ($request['input_types'] as $i => $input) {
                    if ($input == 'file') {
                        $uploadPaths .= ", public string $" . GeneratorUtils::singularCamelCase($request['fields'][$i]) . "Path = ''";
                        $assignUploadPaths .= "\$this->" . GeneratorUtils::singularCamelCase($request['fields'][$i]) . "Path = " . GeneratorUtils::setDiskCodeForController($request['fields'][$i]) . ";\n\t\t";

                        //  Generated code: $image = $generator->image;
                        $assignImageDelete .= "$" . GeneratorUtils::singularCamelCase($request['fields'][$i]) . " = $" . GeneratorUtils::singularCamelCase($model) . "->" . str($request['fields'][$i])->snake() . ";\n\t\t\t";

                        //  Generated code: $this->imageService->delete($this->imagePath . $image);
                        $deleteImageCodes .= "\$this->imageService->delete(image: \$this->" . GeneratorUtils::singularCamelCase($request['fields'][$i]) . "Path . $" . GeneratorUtils::singularCamelCase($request['fields'][$i]) . (config('generator.image.disk') == 's3' ? ", disk: 's3'" : '') . ");\n\t\t\t";

                        // $castImageDataTable .= GeneratorUtils::setDiskCodeForCastImage($model, $request['fields'][$i]);

                        $indexCode .= $this->generateUploadImageCode(
                            field: $request['fields'][$i],
                            path: 'index',
                            model: $modelNameSingularCamelCase,
                            defaultValue: $request['default_values'][$i]
                        );

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

                        $deleteCode .= $this->generateUploadImageCode(
                            field: $request['fields'][$i],
                            path: 'delete',
                            model: $modelNameSingularCamelCase
                        );

                        $castImageFunc .= $this->generateCastImageCode(
                            field: $request['fields'][$i],
                            path: 'index',
                            model: $modelNameSingularCamelCase,
                        ) . "\t\t";
                    }
                }

                /**
                 * Remove $validated = $request->validated(); because is already exist in template (.stub)
                 */
                $passwordFieldStore = str_replace(search: '$validated = $request->validated();', replace: '', subject: $passwordFieldStore);
                $passwordFieldUpdate = str_replace(search: '$validated = $request->validated();', replace: '', subject: $passwordFieldUpdate);

                $inputMonths = str_replace(search: '$validated = $request->validated();', replace: '', subject: $inputMonths);
                $updateDataAction = "\$" . $modelNameSingularCamelCase . "->update(\$validated);";

                if (GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value) {
                    /**
                     *  if($product) {
                     *      $product->update($validated);
                     *  } else {
                     *      Product::create($validated);
                     *  }
                     */
                    $singleFormUpdateDataAction = "$modelNameSingularPascalCase::updateOrCreate(['id' => $" . $modelNameSingularCamelCase . "?->id], " . (str_contains(haystack: $updateDataAction, needle: '$request->validated()') ? '$request->validated()' : '$validated') . ");";

                    $updateDataAction = $singleFormUpdateDataAction;
                }

                /**
                 * controller with upload file code
                 */
                $template = GeneratorUtils::replaceStub(
                    replaces: [
                        'modelNameSingularPascalCase' => $modelNameSingularPascalCase,
                        'modelNameSingularCamelCase' => $modelNameSingularCamelCase,
                        'modelNamePluralCamelCase' => $modelNamePluralCamelCase,
                        'modelNamePluralKebabCase' => $modelNamePluralKebabCase,
                        'modelNameSpaceLowercase' => $modelNameSpaceLowercase,
                        'indexCode' => $indexCode,
                        'storeCode' => $storeCode,
                        'updateCode' => $updateCode,
                        'deleteCode' => $deleteCode,
                        'loadRelation' => $relations,
                        'addColumns' => $addColumns,
                        'query' => $query,
                        'namespace' => $namespace,
                        'requestPath' => $requestPath,
                        'modelPath' => $path != '' ? "App\Models\\$path\\$modelNameSingularPascalCase" : "App\Models\\$modelNameSingularPascalCase",
                        'viewPath' => $path != '' ? str_replace(search: '\\', replace: '.', subject: strtolower($path)) . "." : '',
                        'passwordFieldStore' => $passwordFieldStore,
                        'passwordFieldUpdate' => $passwordFieldUpdate,
                        'updateDataAction' => $updateDataAction,
                        'inputMonths' => $inputMonths,
                        'resourceApiPath' => $path != '' ? "App\Http\Resources\\$path\\$modelNamePluralPascalCase" : "App\Http\Resources\\$modelNamePluralPascalCase",
                        'modelNameCleanSingular' => $modelNameCleanSingular,
                        'modelNameCleanPlural' => $modelNameCleanPlural,
                        'relations' => $relations,
                        'uploadPaths' => $uploadPaths,
                        'assignUploadPaths' => $assignUploadPaths,
                        'assignImageDelete' => $assignImageDelete,
                        'deleteImageCodes' => $deleteImageCodes,
                        'castImageFunction' => $castImageFunc,
                        'castImageIndex' => $castImageIndex,
                        'castImageShow' => "\n\t\t\$this->castImages($$modelNameSingularCamelCase);\n",
                        'middlewareName' => GeneratorUtils::isGenerateApi() ? "'auth:sanctum'," : "'auth',",
                        'useExportNamespace' => $this->generateUseExport($request),
                        'exportFunction' => $this->generateExportFunction($request),
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
                     *
                     */
                    $singleFormUpdateDataAction = "$modelNameSingularPascalCase::updateOrCreate(['id' => $" . $modelNameSingularCamelCase . "?->id], " . (str_contains(haystack: $updateDataAction, needle: '$request->validated()') ? '$request->validated()' : '$validated') . ");";

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
                    'viewPath' => $path != '' ? str_replace(search: '\\', replace: '.', subject: strtolower($path)) . "." : '',
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
                    'exportFunction' => $this->generateExportFunction($request),
                    'useExportNamespace' => $this->generateUseExport($request),
                ], stubName: GeneratorUtils::getControllerStubByGeneratorVariant());
                break;
        }

        if (GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value) {
            $template = str_replace(search: 'created successfully', replace: 'updated successfully', subject: $template);
        }

        /**
         * Create a controller file.
         */
        if (!$path) {
            match (GeneratorUtils::isGenerateApi()) {
                true => GeneratorUtils::checkFolder(app_path("/Http/Controllers/Api")),
                default => GeneratorUtils::checkFolder(app_path("/Http/Controllers")),
            };

            $controllerPath = GeneratorUtils::isGenerateApi() ? "/Api/{$modelNameSingularPascalCase}Controller.php" : "/{$modelNameSingularPascalCase}Controller.php";

            file_put_contents(filename: app_path("/Http/Controllers" . $controllerPath), data: $template);
        } else {
            $fullPath = GeneratorUtils::isGenerateApi() ? app_path("/Http/Controllers/Api/$path/") : app_path("/Http/Controllers/$path/");
            GeneratorUtils::checkFolder($fullPath);

            file_put_contents(filename: "$fullPath{$modelNameSingularPascalCase}Controller.php", data: $template);
        }
    }

    /**
     * Generate an upload file code.
     */
    protected function generateUploadImageCode(string $field, string $path, string|null $model, ?string $defaultValue = null): string
    {
        $default = GeneratorUtils::setDefaultImage(default: $defaultValue, field: $field, model: $model);

        $replaces = [
            'fieldSnakeCase' => str()->snake($field),
            'fieldPluralSnakeCase' => GeneratorUtils::pluralSnakeCase($field),
            'fieldPluralKebabCase' => GeneratorUtils::pluralKebabCase($field),
            'uploadPath' => config('generator.image.disk') == 'storage' ? "storage_path('app/public/uploads" : "public_path('uploads",
            'uploadPathPublic' => config('generator.image.disk') == 'storage' ? "storage/uploads" : "uploads",
            'width' => is_int(config('generator.image.width')) ? config('generator.image.width') : 500,
            'height' => is_int(config('generator.image.height')) ? config('generator.image.height') : 500,
            'aspectRatio' => config('generator.image.aspect_ratio') ? "\n\t\t\t\t\$constraint->aspectRatio();" : '',
            'defaultImageCode' => $default['index_code'],
            'fieldUploadPath' => GeneratorUtils::singularCamelCase($field),
            'defaultImage' => "$" . GeneratorUtils::singularCamelCase($model) . "?->" . str($field)->snake(),
            'fieldCamelCase' => GeneratorUtils::singularCamelCase($field),
            'modelNameSingularCamelCase' => GeneratorUtils::singularCamelCase($model),
            'disk' => config('generator.image.disk') == 's3' ? ", disk: 's3'" : '',
            'castImageDataTable' => GeneratorUtils::setDiskCodeForCastImage(model: $model, field: $field),
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
            'modelNamePluralCamelCase' => GeneratorUtils::pluralCamelCase($model),
            'modelNameSingularCamelCase' => GeneratorUtils::singularCamelCase($model),
            'field' => $field,
            'defaultImage' => config('generator.image.default', 'https://via.placeholder.com/350?text=No+Image+Avaiable'),
            'castImage' => GeneratorUtils::setDiskCodeForCastImage($model, $field),
            'fieldPluralKebabCase' => GeneratorUtils::pluralKebabCase($field),
        ];

        return GeneratorUtils::replaceStub(
            replaces: $replaces,
            stubName: "/controllers/upload-files/cast-image-$path"
        );
    }

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

    public function generateUseExport(array $request): string
    {
        if (isset($request['generate_export']) && $request['generate_export'] == 'on') {
            return "\nuse App\Exports\\" . GeneratorUtils::pluralPascalCase(string: $request['model']) . "Export;\nuse Symfony\Component\HttpFoundation\BinaryFileResponse;";
        }

        return '';
    }
}
