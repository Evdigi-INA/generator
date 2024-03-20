<?php

namespace Tests;

use EvdigiIna\Generator\Generators\GeneratorUtils;
use PHPUnit\Framework\Attributes\Test;

class GeneratorUtilsTest extends TestCase
{
    #[Test]
    public function it_has_get_template_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'getStub'));
    }

    #[Test]
    public function it_has_check_folder_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'checkFolder'));
    }

    #[Test]
    public function it_has_singular_pascal_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'singularPascalCase'));
    }

    #[Test]
    public function it_has_plural_pascal_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'pluralPascalCase'));
    }
    #[Test]
    public function it_has_plural_snake_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'pluralSnakeCase'));
    }

    #[Test]
    public function it_has_singular_snake_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'singularSnakeCase'));
    }

    #[Test]
    public function it_has_plural_camel_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'pluralCamelCase'));
    }

    #[Test]
    public function it_has_kebab_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'kebabCase'));
    }

    #[Test]
    public function it_has_plural_kebab_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'pluralKebabCase'));
    }

    #[Test]
    public function it_has_singular_kebab_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'singularKebabCase'));
    }

    #[Test]
    public function it_has_singular_camel_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'singularCamelCase'));
    }

    #[Test]
    public function it_has_clean_lower_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanLowerCase'));
    }

    #[Test]
    public function it_has_clean_singular_uc_words_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanSingularUcWords'));
    }

    #[Test]
    public function it_has_clean_uc_words_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanUcWords'));
    }

    #[Test]
    public function it_has_clean_plural_lower_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanPluralLowerCase'));
    }

    #[Test]
    public function it_has_get_column_after_id_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'getColumnAfterId'));
    }

    #[Test]
    public function it_has_select_column_after_id_and_id_itself_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'selectColumnAfterIdAndIdItself'));
    }

    #[Test]
    public function it_has_clean_singular_sower_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanSingularLowerCase'));
    }

    #[Test]
    public function it_has_get_model_location_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'getModelLocation'));
    }

    #[Test]
    public function it_has_from_camel_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'fromCamelCase'));
    }

    #[Test]
    public function it_has_set_model_name_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'setModelName'));
    }

    #[Test]
    public function it_has_set_default_image_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'setDefaultImage'));
    }

    #[Test]
    public function it_has_convert_array_sidebar_to_string_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'convertArraySidebarToString'));
    }
}
