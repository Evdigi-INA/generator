<?php

declare(strict_types=1);

namespace EvdigiIna\Generator\Generators;

class TestGenerator
{
    public function generate(array $request): void
    {
        if (empty($request['generate_tests'])) {
            return;
        }

        $model = GeneratorUtils::setModelName($request['model'], 'default');
        $name = GeneratorUtils::singularPascalCase($model);
        $pluralKebab = GeneratorUtils::pluralKebabCase($model);
        $singularCamel = GeneratorUtils::singularCamelCase($model);
        $pluralPascal = GeneratorUtils::pluralPascalCase($model);

        $path = base_path("tests/Feature/{$pluralPascal}");
        GeneratorUtils::checkFolder($path);

        $content = GeneratorUtils::replaceStub(
            replaces: [
                '{{ modelName }}' => $name,
                '{{ modelPluralPascal }}' => $pluralPascal,
                '{{ modelPluralKebab }}' => $pluralKebab,
                '{{ modelSingularCamel }}' => $singularCamel,
                '{{ modelSingularKebab }}' => GeneratorUtils::singularKebabCase($model),
                '{{ modelPluralSnake }}' => GeneratorUtils::pluralSnakeCase($model),
                '{{ fields.0.name }}' => $request['fields'][0] ?? 'name',
            ],
            stubName: 'test'
        );

        file_put_contents("{$path}/{$name}Test.php", $content);
    }
}
