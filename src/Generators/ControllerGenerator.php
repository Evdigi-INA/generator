<?php

namespace EvdigiIna\Generator\Generators;

use Illuminate\Support\Facades\Log;

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

                if (GeneratorUtils::isGenerateApi()) {
                    $namespace = "namespace App\Http\Controllers\Api;\n\nuse App\Http\Controllers\Controller;";
                }

                /**
                 * will generate something like:
                 *
                 * use App\Http\Requests\{StoreProductRequest, UpdateProductRequest};
                 */
                $requestPath = "App\Http\Requests\\$modelNamePluralPascalCase\{Store" . $modelNameSingularPascalCase . "Request, Update" . $modelNameSingularPascalCase . "Request}";
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
                $requestPath = "App\Http\Requests\\$path\\$modelNamePluralPascalCase\{Store" . $modelNameSingularPascalCase . "Request, Update" . $modelNameSingularPascalCase . "Request}";
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
                if ($type == 'text' || $type == 'longText') {
                    $addColumns .= "->addColumn('" . str($request['fields'][$i])->snake() . "', function(\$row){
                    return str(\$row->" . str($request['fields'][$i])->snake() . ")->limit($limitText);
                })\n\t\t\t\t";
                }
            }
        }

        // load the relations for create, show, and edit
        if (in_array('foreignId', $request['column_types'])) {

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
                     *     return $row->category ? $row->category->name : '-';
                     * })
                     */
                    $addColumns .= "->addColumn('$constrainSnakeCase', function (\$row) {
                    return \$row->" . $constrainSnakeCase . " ? \$row->" . $constrainSnakeCase . "->$columnAfterId : '';
                })";
                }
            }

            $query .= ")";
            $relations .= ");\n\n\t\t";

            $query = str_replace("''", "', '", $query);
            $relations = str_replace("''", "', '", $relations);
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

                    /**
                     * will generate something like:
                     *
                     * switch (is_null($request->password)) {
                     *   case true:
                     *      unset($validated['password']);
                     *       break;
                     *   default:
                     *       $validated['password'] = bcrypt($request->password);
                     *       break;
                     *   }
                     */
                    $passwordFieldUpdate .= "
        if (is_null(\$request->" . str()->snake($request['fields'][$i]) . ")) {
            unset(\$validated['" . str()->snake($request['fields'][$i]) . "']);
        } else {
            \$validated['" . str()->snake($request['fields'][$i]) . "'] = bcrypt(\$request->" . str()->snake($request['fields'][$i]) . ");
        }\n";
                }
            }
        }

        /**
         * Generate code for insert input type month with datatype date.
         * by default will getting an error, cause invalid format.
         */
        $inputMonths = "";
        if (in_array('month', $request['input_types'])) {
            if (!in_array('password', $request['input_types'])) {
                /**
                 * don't concat string if any input type password, cause already concating ahead.
                 */
                $inputMonths .= $requestValidatedAttr;
            }

            foreach ($request['input_types'] as $i => $input) {
                if ($input === 'month') {
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

                foreach ($request['input_types'] as $i => $input) {
                    if ($input == 'file') {
                        $indexCode .= $this->generateUploadFileCode(
                            field: $request['fields'][$i],
                            path: 'index',
                            defaultValue: $request['default_values'][$i],
                            model: $modelNameSingularCamelCase
                        );

                        $storeCode .= $this->generateUploadFileCode(
                            field: $request['fields'][$i],
                            path: 'store',
                            model: $modelNameSingularCamelCase
                        );

                        $updateCode .= $this->generateUploadFileCode(
                            field: $request['fields'][$i],
                            path: 'update',
                            model: $modelNameSingularCamelCase
                        );

                        $deleteCode .= $this->generateUploadFileCode(
                            field: $request['fields'][$i],
                            path: 'delete',
                            model: $modelNameSingularCamelCase
                        );
                    }
                }

                /**
                 * Remove $validated = $request->validated(); because is already exist in template (.stub)
                 */
                $passwordFieldStore = str_replace('$validated = $request->validated();', '', $passwordFieldStore);
                $passwordFieldUpdate = str_replace('$validated = $request->validated();', '', $passwordFieldUpdate);

                Log::info('variant', [$request['generate_variant']]);

                $inputMonths = str_replace('$validated = $request->validated();', '', $inputMonths);
                $updateDataAction = "\$"  .  $modelNameSingularCamelCase  .  "->update(\$validated);";

                if ($request['generate_variant'] == 'api') {
                    $getTemplate = GeneratorUtils::getTemplate('controllers/controller-api-with-upload-file');
                } else {
                    $getTemplate = GeneratorUtils::getTemplate('controllers/controller-with-upload-file');
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
                        '{{modelNameCleanPlural}}'
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
                        $path != '' ? "App\Models\\$path\\$modelNameSingularPascalCase" : "App\Models\\$modelNameSingularPascalCase",
                        $path != '' ? str_replace('\\', '.', strtolower($path)) . "." : '',
                        $passwordFieldStore,
                        $passwordFieldUpdate,
                        $updateDataAction,
                        $inputMonths,
                        $path != '' ? "App\Http\Resources\\$path\\$modelNamePluralPascalCase" : "App\Http\Resources\\$modelNamePluralPascalCase",
                        $modelNameCleanSingular,
                        $modelNameCleanPlural
                    ],
                    $getTemplate
                );
                break;
            default:
                if ($request['generate_variant'] == 'api') {
                    $getTemplate = GeneratorUtils::getTemplate('controllers/controller-api');
                } else {
                    $getTemplate = GeneratorUtils::getTemplate('controllers/controller');
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
                        '{{modelNameCleanPlural}}'
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
                        $modelNameCleanPlural
                    ],
                    $getTemplate
                );
                break;
        }

        /**
         * Create a controller file.
         */
        if (!$path) {
            GeneratorUtils::checkFolder(app_path("/Http/Controllers/Api"));

            if (GeneratorUtils::isGenerateApi()) {
                file_put_contents(app_path("/Http/Controllers/Api/{$modelNameSingularPascalCase}Controller.php"), $template);
            } else {
                file_put_contents(app_path("/Http/Controllers/{$modelNameSingularPascalCase}Controller.php"), $template);
            }
        } else {
            if (GeneratorUtils::isGenerateApi()) {
                $fullPath = app_path("/Http/Controllers/Api/$path/");
            } else {
                $fullPath = app_path("/Http/Controllers/$path/");
            }

            GeneratorUtils::checkFolder($fullPath);

            file_put_contents("$fullPath" . $modelNameSingularPascalCase . "Controller.php", $template);
        }

        Log::info('Controller created successfully.', [
            'generated_code' => $template,
            'is_api' => GeneratorUtils::isGenerateApi(),
            'request' => $request
        ]);
    }

    /**
     * Generate an upload file code.
     */
    protected function generateUploadFileCode(string $field, string $path, string $model, ?string $defaultValue = null): string
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
        ];

        $default = GeneratorUtils::setDefaultImage(default: $defaultValue, field: $field, model: $model);

        $replaceWith = [
            str()->snake($field),
            GeneratorUtils::pluralSnakeCase($field),
            GeneratorUtils::pluralKebabCase($field),
            config('generator.image.path') == 'storage' ? "storage_path('app/public/uploads" : "public_path('uploads",
            config('generator.image.path') == 'storage' ? "storage/uploads" : "uploads",
            is_int(config('generator.image.width')) ? config('generator.image.width') : 500,
            is_int(config('generator.image.height')) ? config('generator.image.height') : 500,
            config('generator.image.aspect_ratio') ? "\n\t\t\t\t\$constraint->aspectRatio();" : '',
            $default['index_code'],
        ];

        if ($model != null) {
            array_push($replaceString, '{{modelNameSingularCamelCase}}');
            array_push($replaceWith, $model);
        }

        switch (config('generator.image.crop')) {
            case true:
                return str_replace(
                    $replaceString,
                    $replaceWith,
                    GeneratorUtils::getTemplate("controllers/upload-files/with-crop/$path")
                );
                break;
            default:
                return str_replace(
                    $replaceString,
                    $replaceWith,
                    GeneratorUtils::getTemplate("controllers/upload-files/$path")
                );
                break;
        }
    }
}
