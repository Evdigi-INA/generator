<?php

namespace Tests;

use EvdigiIna\Generator\Generators\GeneratorUtils;
use PHPUnit\Framework\Attributes\Test;

class GeneratorUtilsTest extends TestCase
{
    #[Test]
    public function it_has_get_stub_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'getStub'));
    }

    #[Test]
    public function it_has_get_published_files_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'getPublishedFiles'));
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
    public function it_has_pascal_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'pascalCase'));
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
    public function it_has_singular_camel_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'singularCamelCase'));
    }

    #[Test]
    public function it_has_plural_kebab_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'pluralKebabCase'));
    }

    #[Test]
    public function it_has_kebab_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'kebabCase'));
    }

    #[Test]
    public function it_has_singular_kebab_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'singularKebabCase'));
    }

    #[Test]
    public function it_has_clean_singular_lower_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanSingularLowerCase'));
    }

    #[Test]
    public function it_has_clean_lower_case_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanLowerCase'));
    }

    #[Test]
    public function it_has_clean_plural_uc_words_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'cleanPluralUcWords'));
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
    public function it_has_check_string_end_with_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'checkStringEndWith'));
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

    #[Test]
    public function it_has_is_active_menu_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'isActiveMenu'));
    }

    #[Test]
    public function it_has_is_generate_api_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'isGenerateApi'));
    }

    #[Test]
    public function it_has_check_package_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'checkPackage'));
    }

    #[Test]
    public function it_has_check_package_version_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'checkPackageVersion'));
    }

    #[Test]
    public function it_has_get_composer_package_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'getComposerPackage'));
    }

    #[Test]
    public function it_has_set_disk_code_for_controller_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'setDiskCodeForController'));
    }

    #[Test]
    public function it_has_set_disk_code_for_cast_image_method(): void
    {
        $this->assertTrue(method_exists(GeneratorUtils::class, 'setDiskCodeForCastImage'));
    }

    #[Test]
    public function it_resolves_blade_controller_stub(): void
    {
        $content = GeneratorUtils::getStub(path: 'controllers/controller');

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('<?php', $content);
    }

    #[Test]
    public function it_resolves_shared_model_stub(): void
    {
        $content = GeneratorUtils::getStub(path: 'model');

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('<?php', $content);
    }

    #[Test]
    public function it_resolves_blade_route_stub(): void
    {
        $content = GeneratorUtils::getStub(path: 'route');

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('Route::', $content);
    }

    #[Test]
    public function it_resolves_api_stub_from_shared(): void
    {
        $content = GeneratorUtils::getStub(path: 'api');

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('Route::', $content);
    }

    #[Test]
    public function it_falls_back_to_shared_when_target_directory_lacks_stub(): void
    {
        $content = GeneratorUtils::getStub(path: 'export', target: 'api');

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('<?php', $content);
    }

    #[Test]
    public function it_falls_back_to_blade_as_last_resort(): void
    {
        $content = GeneratorUtils::getStub(path: 'route', target: 'api');

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('Route::', $content);
    }

    #[Test]
    public function it_throws_exception_for_nonexistent_stub(): void
    {
        $this->expectException(\RuntimeException::class);

        GeneratorUtils::getStub(path: 'nonexistent-stub-that-does-not-exist');
    }

    #[Test]
    public function it_resolves_shared_sidebar_dynamic_stub(): void
    {
        $content = GeneratorUtils::getStub(path: 'sidebar-dynamic');

        $this->assertNotEmpty($content);
    }

    #[Test]
    public function it_resolves_user_role_api_stub(): void
    {
        $content = GeneratorUtils::getStub(path: 'user-role-api');

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('Route::', $content);
    }

    #[Test]
    public function it_resolves_with_explicit_target(): void
    {
        $content = GeneratorUtils::getStub(path: 'controllers/controller', target: 'blade');

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('<?php', $content);
    }

    #[Test]
    public function it_resolves_shared_model_cast_stub(): void
    {
        $content = GeneratorUtils::getStub(path: 'model-cast');

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('Attribute', $content);
    }
}
