<?php

namespace Tests;

use Zzzul\Generator\Generators\GeneratorUtils;

class GeneratorUtilsTest extends TestCase
{
    /** @test */
    public function it_has_get_template_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'getTemplate'));
    }

    /** @test */
    public function it_has_check_folder_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'checkFolder'));
    }

    /** @test */
    public function it_has_singular_pascal_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'singularPascalCase'));
    }

    /** @test */
    public function it_has_plural_pascal_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'pluralPascalCase'));
    }
    /** @test */
    public function it_has_plural_snake_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'pluralSnakeCase'));
    }

    /** @test */
    public function it_has_singular_snake_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'singularSnakeCase'));
    }

    /** @test */
    public function it_has_plural_camel_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'pluralCamelCase'));
    }

    /** @test */
    public function it_has_kebab_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'kebabCase'));
    }

    /** @test */
    public function it_has_plural_kebab_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'pluralKebabCase'));
    }

    /** @test */
    public function it_has_singular_kebab_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'singularKebabCase'));
    }

    /** @test */
    public function it_has_singular_camel_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'singularCamelCase'));
    }

    /** @test */
    public function it_has_clean_lower_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanLowerCase'));
    }

    /** @test */
    public function it_has_clean_singular_uc_words_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanSingularUcWords'));
    }

    /** @test */
    public function it_has_clean_uc_words_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanUcWords'));
    }

    /** @test */
    public function it_has_clean_plural_lower_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanPluralLowerCase'));
    }

    /** @test */
    public function it_has_get_column_after_id_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'getColumnAfterId'));
    }

    /** @test */
    public function it_has_select_column_after_id_and_id_itself_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'selectColumnAfterIdAndIdItself'));
    }

    /** @test */
    public function it_has_clean_singular_sower_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanSingularLowerCase'));
    }

    /** @test */
    public function it_has_get_model_location_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'getModelLocation'));
    }

    /** @test */
    public function it_has_from_camel_case_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'fromCamelCase'));
    }

    /** @test */
    public function it_has_set_model_name_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'setModelName'));
    }

    /** @test */
    public function it_has_set_default_image_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'setDefaultImage'));
    }

    /** @test */
    public function it_has_convert_array_sidebar_to_string_method()
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'convertArraySidebarToString'));
    }
}
