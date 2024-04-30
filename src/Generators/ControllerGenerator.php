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

                if (GeneratorUtils::isGenerateApi()) $namespace = "namespace App\Http\Controllers\Api;\n\nuse App\Http\Controllers\Controller;";

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

                if (GeneratorUtils::isGenerateApi()) $namespace = "namespace App\Http\Controllers\Api\\$path;\n\nuse App\Http\Controllers\Controller;";

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

                        if ($countForeignId + 1 < $i) {
                            $relations .= "'$constrainSnakeCase:$selectedColumns', ";
                            $query .= "'$constrainSnakeCase:$selectedColumns', ";
                        } else {
                            $relations .= "'$constrainSnakeCase:$selectedColumns'";
                            $query .= "'$constrainSnakeCase:$selectedColumns'";
                        }
                    }
                }

                $relations .= "])->";
            } else {
                $relations .= "$" . $modelNameSingularCamelCase . "->load(";

                $countForeignId = count(array_keys($request['column_types'], 'foreignId'));

                $query = "$modelNameSingularPascalCase::with(";

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

                $query .= ")";
                $relations .= ");\n\n\t\t";

                $query = str_replace("''", "', '", $query);
                $relations = str_replace("''", "', '", $relations);
            }
        }

        /**
         * will generate something like:
         *
         * User::create($request->validated());
         * $user->update($request->validated());
         */
        $insertDataAction = "$$modelNameSingularCamelCase = " . $modelNameSingularPascalCase  . "::create(\$request->validated());";
        $updateDataAction = "\$"  .  $modelNameSingularCamelCase  .  "->update(\$request->validated());";
        $requestValidatedAttr = "";

        if (in_array('password', $request['input_types']) || in_array('month', $request['input_types'])) {
            /**
             * * will generate something like:
             *
             *  User::create($validated);
             *  $user->update($validated);
             */
            $insertDataAction = "$$modelNameSingularCamelCase = " . $modelNameSingularPascalCase  . "::create(\$validated);";
            $updateDataAction = "\$"  .  $modelNameSingularCamelCase  .  "->update(\$validated);";
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
        if (in_array('month', $request['input_types'])) {
            if (!in_array('password', $request['input_types'])) {
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
        switch (in_array('file', $request['input_types'])) {
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
                        $assignImageDelete .= "$" . str($request['fields'][$i])->snake() . " = $" . GeneratorUtils::singularCamelCase($model) . "->" . str($request['fields'][$i])->snake() . ";\n\t\t\t";

                        //  Generated code: $this->imageService->delete($this->imagePath . $image);
                        $deleteImageCodes .= "\$this->imageService->delete(image: \$this->" . GeneratorUtils::singularCamelCase($request['fields'][$i]) . "Path . $" . str($request['fields'][$i])->snake() . (config('generator.image.disk') == 's3' ? ", disk: 's3'" : '') . ");\n\t\t\t";

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
                $passwordFieldStore = str_replace('$validated = $request->validated();', '', $passwordFieldStore);
                $passwordFieldUpdate = str_replace('$validated = $request->validated();', '', $passwordFieldUpdate);

                $inputMonths = str_replace('$validated = $request->validated();', '', $inputMonths);
                $updateDataAction = "\$"  .  $modelNameSingularCamelCase  .  "->update(\$validated);";

                if (GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value) {
                    /**
                     *  if($product) {
                     *      $product->update($validated);
                     *  } else {
                     *      Product::create($validated);
                     *  }
                     */
                    $singleFormUpdateDataAction = "$modelNameSingularPascalCase::updateOrCreate(['id' => $" .$modelNameSingularCamelCase ."?->id], " . (str_contains($updateDataAction, '$request->validated()') ? '$request->validated()' : '$validated') . ");";

                    $updateDataAction = $singleFormUpdateDataAction;
                }

                /**
                 * controller with upload file code
                 */
                $template = str_replace(
                    [
                        '{{modelNameSingularPascalCase}}',
                        '{{modelNameSingularCamelCase}}',
                        '{{modelNamePluralCamelCase}}',
                        '{{modelNamePluralKebabCase}}',
                        '{{modelNameSpaceLowercase}}',
                        '{{indexCode}}',
                        '{{storeCode}}',
                        '{{updateCode}}',
                        '{{deleteCode}}',
                        '{{loadRelation}}',
                        '{{addColumns}}',
                        '{{query}}',
                        '{{namespace}}',
                        '{{requestPath}}',
                        '{{modelPath}}',
                        '{{viewPath}}',
                        '{{passwordFieldStore}}',
                        '{{passwordFieldUpdate}}',
                        '{{updateDataAction}}',
                        '{{inputMonths}}',
                        '{{resourceApiPath}}',
                        '{{modelNameCleanSingular}}',
                        '{{modelNameCleanPlural}}',
                        '{{relations}}',
                        '{{uploadPaths}}',
                        '{{assignUploadPaths}}',
                        '{{assignImageDelete}}',
                        '{{deleteImageCodes}}',
                        '{{castImageFunction}}',
                        '{{castImageIndex}}',
                        '{{castImageShow}}',
                        // '{{castImageDataTable}}',
                        "'{{middlewareName}}',",
                    ],
                    [
                        $modelNameSingularPascalCase,
                        $modelNameSingularCamelCase,
                        $modelNamePluralCamelCase,
                        $modelNamePluralKebabCase,
                        $modelNameSpaceLowercase,
                        $indexCode,
                        $storeCode,
                        $updateCode,
                        $deleteCode,
                        $relations,
                        $addColumns,
                        $query,
                        $namespace,
                        $requestPath,

                        // App\Models\Product
                        $path != '' ? "App\Models\\$path\\$modelNameSingularPascalCase" : "App\Models\\$modelNameSingularPascalCase",
                        $path != '' ? str_replace('\\', '.', strtolower($path)) . "." : '',
                        $passwordFieldStore,
                        $passwordFieldUpdate,
                        $updateDataAction,
                        $inputMonths,

                        // App\Http\Resources\ProductResource
                        $path != '' ? "App\Http\Resources\\$path\\$modelNamePluralPascalCase" : "App\Http\Resources\\$modelNamePluralPascalCase",
                        $modelNameCleanSingular,
                        $modelNameCleanPlural,
                        $relations,
                        $uploadPaths,
                        $assignUploadPaths,
                        $assignImageDelete,
                        $deleteImageCodes,
                        $castImageFunc,
                        $castImageIndex,
                        // $this->castImages($product);
                        "\n\t\t\$this->castImages($" . $modelNameSingularCamelCase . ");\n",
                        // $castImageDataTable,
                        GeneratorUtils::isGenerateApi() ? "'auth:sanctum'," : "'auth',",
                    ],
                    GeneratorUtils::getStub(GeneratorUtils::getControllerStubByGeneratorVariant(true))
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
                    $singleFormUpdateDataAction = "$modelNameSingularPascalCase::updateOrCreate(['id' => $" .$modelNameSingularCamelCase ."?->id], " . (str_contains($updateDataAction, '$request->validated()') ? '$request->validated()' : '$validated') . ");";

                    $updateDataAction = $singleFormUpdateDataAction;
                }

                if (!GeneratorUtils::isGenerateApi()) {
                    // remove unused variable
                    // $user = User::create($request->validated());
                    $insertDataAction = str_replace(
                        "$$modelNameSingularCamelCase = " . $modelNameSingularPascalCase  . "::create(\$request->validated());",
                        $modelNameSingularPascalCase  . "::create(\$request->validated());",
                        $insertDataAction
                    );
                }

                /**
                 * default controller
                 */
                $template = str_replace(
                    [
                        '{{modelNameSingularPascalCase}}',
                        '{{modelNameSingularCamelCase}}',
                        '{{modelNamePluralCamelCase}}',
                        '{{modelNamePluralKebabCase}}',
                        '{{modelNameSpaceLowercase}}',
                        '{{loadRelation}}',
                        '{{addColumns}}',
                        '{{query}}',
                        '{{namespace}}',
                        '{{requestPath}}',
                        '{{modelPath}}',
                        '{{viewPath}}',
                        '{{passwordFieldStore}}',
                        '{{passwordFieldUpdate}}',
                        '{{insertDataAction}}',
                        '{{updateDataAction}}',
                        '{{inputMonths}}',
                        '{{resourceApiPath}}',
                        '{{modelNameCleanSingular}}',
                        '{{modelNameCleanPlural}}',
                        '{{relations}}',
                        '{{publicOrStorage}}',
                        "'{{middlewareName}}',",
                    ],
                    [
                        $modelNameSingularPascalCase,
                        $modelNameSingularCamelCase,
                        $modelNamePluralCamelCase,
                        $modelNamePluralKebabCase,
                        $modelNameSpaceLowercase,
                        $relations,
                        $addColumns,
                        $query,
                        $namespace,
                        $requestPath,
                        $path != '' ? "App\Models\\$path\\$modelNameSingularPascalCase" : "App\Models\\$modelNameSingularPascalCase",
                        $path != '' ? str_replace('\\', '.', strtolower($path)) . "." : '',
                        $passwordFieldStore,
                        $passwordFieldUpdate,
                        $insertDataAction,
                        $updateDataAction,
                        $inputMonths,
                        $path != '' ? "App\Http\Resources\\$path\\$modelNamePluralPascalCase" : "App\Http\Resources\\$modelNamePluralPascalCase",
                        $modelNameCleanSingular,
                        $modelNameCleanPlural,
                        $relations,
                        config('generator.image.disk', 'storage'),
                        GeneratorUtils::isGenerateApi() ? "'auth:sanctum'," : "'auth',",
                    ],
                    GeneratorUtils::getStub(GeneratorUtils::getControllerStubByGeneratorVariant())
                );
                break;
        }

        if (GeneratorUtils::checkGeneratorVariant() == GeneratorVariant::SINGLE_FORM->value) {
            $template = str_replace('created successfully', 'updated successfully', $template);
        }

        /**
         * Create a controller file.
         */
        if (!$path) {
            match (GeneratorUtils::isGenerateApi()) {
                true => GeneratorUtils::checkFolder(app_path("/Http/Controllers/Api")),
                false => GeneratorUtils::checkFolder(app_path("/Http/Controllers")),
            };

            $controllerPath = GeneratorUtils::isGenerateApi() ? "/Api/{$modelNameSingularPascalCase}Controller.php" : "/{$modelNameSingularPascalCase}Controller.php";

            file_put_contents(app_path("/Http/Controllers" . $controllerPath), $template);
        } else {
            $fullPath = GeneratorUtils::isGenerateApi() ? app_path("/Http/Controllers/Api/$path/") : app_path("/Http/Controllers/$path/");
            GeneratorUtils::checkFolder($fullPath);

            file_put_contents("$fullPath{$modelNameSingularPascalCase}Controller.php", $template);
        }
    }

    /**
     * Generate an upload file code.
     */
    protected function generateUploadImageCode(string $field, string $path, string|null $model, ?string $defaultValue = null): string
    {
        $replaceString = [
            '{{fieldSnakeCase}}',
            '{{fieldPluralSnakeCase}}',
            '{{fieldPluralKebabCase}}',
            '{{uploadPath}}',
            '{{uploadPathPublic}}',
            '{{width}}',
            '{{height}}',
            '{{aspectRatio}}',
            '{{defaultImageCode}}',
            '{{fieldUploadPath}}',
            '{{defaultImage}}',
            '{{fieldCamelCase}}',
            '{[modelNameSingularCamelCase}}',
            '{{disk}}',
            '{{castImageDataTable}}',
        ];

        $default = GeneratorUtils::setDefaultImage(default: $defaultValue, field: $field, model: $model);

        $replaceWith = [
            str()->snake($field),
            GeneratorUtils::pluralSnakeCase($field),
            GeneratorUtils::pluralKebabCase($field),
            config('generator.image.disk') == 'storage' ? "storage_path('app/public/uploads" : "public_path('uploads",
            config('generator.image.disk') == 'storage' ? "storage/uploads" : "uploads",
            is_int(config('generator.image.width')) ? config('generator.image.width') : 500,
            is_int(config('generator.image.height')) ? config('generator.image.height') : 500,
            config('generator.image.aspect_ratio') ? "\n\t\t\t\t\$constraint->aspectRatio();" : '',
            $default['index_code'],
            str($field)->snake(),
            "$" . GeneratorUtils::singularCamelCase($model) . "?->" . str($field)->snake(),
            GeneratorUtils::singularCamelCase($field),
            GeneratorUtils::singularCamelCase($model),
            config('generator.image.disk') == 's3' ? ", disk: 's3'" : '',
            GeneratorUtils::setDiskCodeForCastImage($model, $field)
        ];

        if ($model) {
            $replaceString[] = '{{modelNameSingularCamelCase}}';
            $replaceWith[] = $model;
        }

        return str_replace(
            $replaceString,
            $replaceWith,
            GeneratorUtils::getStub("controllers/upload-files/$path")
        );
    }

    /**
     * Generate a cast image code.
     */
    public function generateCastImageCode(string $field, string $path, string $model): string
    {
        return str_replace([
            '{{modelNamePluralCamelCase}}',
            '{{modelNameSingularCamelCase}}',
            '{{field}}',
            '{{defaultImage}}',
            '{{castImage}}',
            '{{fieldPluralKebabCase}}'
        ], [
            GeneratorUtils::pluralCamelCase($model),
            GeneratorUtils::singularCamelCase($model),
            $field,
            config('generator.image.default', 'https://via.placeholder.com/350?text=No+Image+Avaiable'),
            GeneratorUtils::setDiskCodeForCastImage($model, $field),
            GeneratorUtils::pluralKebabCase($field),
        ], GeneratorUtils::getStub('/controllers/upload-files/cast-image-' . $path));
    }
}
