<?php

namespace Zzzul\Generator\Generators;

class ControllerGenerator
{
    /**
     * Generate a controller file.
     *
     * @param array $request
     * @return void
     */
    public function generate(array $request)
    {
        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $path = GeneratorUtils::getModelLocation($request['model']);

        $modelNameSingularCamelCase = GeneratorUtils::singularCamelCase($model);
        $modelNamePluralCamelCase = GeneratorUtils::pluralCamelCase($model);
        $modelNamePluralKebabCase = GeneratorUtils::pluralKebabCase($model);
        $modelNameSpaceLowercase = GeneratorUtils::cleanSingularLowerCase($model);
        $modelNameSingularPascalCase = GeneratorUtils::singularPascalCase($model);

        $query = "$modelNameSingularPascalCase::query()";

        switch ($path) {
            case '':
                $namespace = "namespace App\Http\Controllers;\n";

                /**
                 * will generate something like:
                 *
                 * use App\Http\Requests\{StoreProductRequest, UpdateProductRequest};
                 */
                $requestPath = "App\Http\Requests\{Store" . $modelNameSingularPascalCase . "Request, Update" . $modelNameSingularPascalCase . "Request}";
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

                /**
                 * Will generate something like:
                 *
                 * use App\Http\Requests\Inventory\{StoreProductRequest, UpdateProductRequest};
                 */
                $requestPath = "App\Http\Requests\\" . $path . "\{Store" . $modelNameSingularPascalCase . "Request, Update" . $modelNameSingularPascalCase . "Request}";
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

            $countForeidnId = count(array_keys($request['column_types'], 'foreignId'));

            $query = "$modelNameSingularPascalCase::with(";

            foreach ($request['constrains'] as $i => $constrain) {
                if ($constrain != null) {
                    // remove path or '/' if exists
                    $constrainName = GeneratorUtils::setModelName($request['constrains'][$i]);

                    $constrainSnakeCase = GeneratorUtils::singularSnakeCase($constrainName);
                    $selectedColumns = GeneratorUtils::selectColumnAfterIdAndIdItself($constrainName);
                    $columnAfterId = GeneratorUtils::getColumnAfterId($constrainName);

                    if($countForeidnId + 1 < $i){
                        $relations .= "'$constrainSnakeCase:$selectedColumns', ";
                        $query .= "'$constrainSnakeCase:$selectedColumns', ";
                    }else{
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
        $insertDataAction = $modelNameSingularPascalCase  . "::create(\$request->validated());";
        $updateDataAction = "\$"  .  $modelNameSingularCamelCase  .  "->update(\$request->validated());";
        $requestValidatedAttr = "";

        if (in_array('password', $request['input_types']) || in_array('month', $request['input_types'])) {
            /**
             * * will generate something like:
             *
             *  User::create($attr);
             *  $user->update($attr);
             */
            $insertDataAction = $modelNameSingularPascalCase  . "::create(\$attr);";
            $updateDataAction = "\$"  .  $modelNameSingularCamelCase  .  "->update(\$attr);";
            $requestValidatedAttr = "\$attr = \$request->validated();\n";
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
                     * $attr['password'] = bcrypt($request->password);
                     */
                    $passwordFieldStore .= "\t\t\$attr['" . str()->snake($request['fields'][$i]) . "'] = bcrypt(\$request->" . str()->snake($request['fields'][$i]) . ");\n";

                    /**
                     * will generate something like:
                     *
                     * switch (is_null($request->password)) {
                     *   case true:
                     *      unset($attr['password']);
                     *       break;
                     *   default:
                     *       $attr['password'] = bcrypt($request->password);
                     *       break;
                     *   }
                     */
                    $passwordFieldUpdate .= "
        switch (is_null(\$request->" . str()->snake($request['fields'][$i]) . ")) {
            case true:
                unset(\$attr['" . str()->snake($request['fields'][$i]) . "']);
                break;
            default:
                \$attr['" . str()->snake($request['fields'][$i]) . "'] = bcrypt(\$request->" . str()->snake($request['fields'][$i]) . ");
                break;
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
                 * dont concat string if any input type password, cause already concating ahead.
                 */
                $inputMonths .= $requestValidatedAttr;
            }

            foreach ($request['input_types'] as $i => $input) {
                if ($input === 'month') {
                    /**
                     * will generate something like:
                     *
                     * $attr['month'] = $request->month ? \Carbon\Carbon::createFromFormat('Y-m', $request->month)->toDateTimeString() : null;
                     */
                    $inputMonths .= "\t\t\$attr['" . str()->snake($request['fields'][$i]) . "'] = \$request->" . str()->snake($request['fields'][$i]) . " ? \Carbon\Carbon::createFromFormat('Y-m', \$request->" . str()->snake($request['fields'][$i]) . ")->toDateTimeString() : null;\n";
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
                 * Remove $attr = $request->validated(); because is already exist in template (.stub)
                 */
                $passwordFieldStore = str_replace('$attr = $request->validated();', '', $passwordFieldStore);
                $passwordFieldUpdate = str_replace('$attr = $request->validated();', '', $passwordFieldUpdate);

                $inputMonths = str_replace('$attr = $request->validated();', '', $inputMonths);
                $updateDataAction = "\$"  .  $modelNameSingularCamelCase  .  "->update(\$attr);";

                /**
                 * controller with upload file code
                 */
                $template = str_replace(
                    [
                        '{{modelNameSingularPascalCase}}',
                        '{{modelNameSingularCamelCase}}',
                        '{{modelNamePluralCamleCase}}',
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
                        $inputMonths
                    ],
                    GeneratorUtils::getTemplate('controllers/controller-with-upload-file')
                );
                break;
            default:
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
                        $inputMonths
                    ],
                    GeneratorUtils::getTemplate('controllers/controller')
                );
                break;
        }

        /**
         * Create a controller file.
         */
        switch ($path) {
            case '':
                file_put_contents(app_path("/Http/Controllers/{$modelNameSingularPascalCase}Controller.php"), $template);
                break;
            default:
                $fullPath = app_path("/Http/Controllers/$path/");
                GeneratorUtils::checkFolder($fullPath);
                file_put_contents("$fullPath" . $modelNameSingularPascalCase . "Controller.php", $template);
                break;
        }
    }

    /**
     * Generate an upload file code.
     *
     * @param string $field
     * @param string $path
     * @param string $model
     * @param ?string $defaultValue
     * @return string
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
