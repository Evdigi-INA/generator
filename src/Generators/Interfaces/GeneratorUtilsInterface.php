<?php

namespace EvdigiIna\Generator\Generators\Interfaces;

interface GeneratorUtilsInterface
{
    /**
     * Get template/stub file.
     */
    public static function getStub(string $path): string;

    /**
     * Get published files.
     */
    public static function getPublishedFiles(string $path): string;

    /**
     * Check folder if not exist, then make folder.
     */
    public static function checkFolder(string $path): void;

    /**
     * Convert string to singular pascal case.
     */
    public static function singularPascalCase(string $string): string;

    /**
     * Convert string to pascal case.
     */
    public static function pascalCase(string $string): string;

    /**
     * Convert string to plural pascal case.
     */
    public static function pluralPascalCase(string $string): string;

    /**
     * Convert string to plural snake case.
     */
    public static function pluralSnakeCase(string $string): string;

    /**
     * Convert string to singular snake case.
     */
    public static function singularSnakeCase(string $string): string;

    /**
     * Convert string to plural camel case.
     */
    public static function pluralCamelCase(string $string): string;

    /**
     * Convert string to singular camel case.
     */
    public static function singularCamelCase(string $string): string;

    /**
     * Convert string to plural kebab case.
     */
    public static function pluralKebabCase(string $string): string;

    /**
     * Convert string to kebab case.
     */
    public static function kebabCase(string $string): string;

    /**
     * Convert string to singular kebab case.
     */
    public static function singularKebabCase(string $string): string;

    /**
     * Convert string to singular, clean lowercase.
     */
    public static function cleanSingularLowerCase(string $string): string;

    /**
     * Convert string to clean lowercase.
     */
    public static function cleanLowerCase(string $string): string;

    /**
     * Convert string to plural, clean uppercase words.
     */
    public static function cleanPluralUcWords(string $string): string;

    /**
     * Convert string to singular, clean uppercase words.
     */
    public static function cleanSingularUcWords(string $string): string;

    /**
     * Convert string to clean uppercase words.
     */
    public static function cleanUcWords(string $string): string;

    /**
     * Convert string to plural, clean lowercase.
     */
    public static function cleanPluralLowerCase(string $string): string;

    /**
     * Check if string ends with 'ia' or 'ium'.
     */
    public static function checkStringEndWith(string $string): bool;

    /**
     * Get column after id in table.
     */
    public static function getColumnAfterId(string $table): string;

    /**
     * Select column after id and id itself.
     */
    public static function selectColumnAfterIdAndIdItself(string $table): string;

    /**
     * Get model location/path.
     */
    public static function getModelLocation(string $model): string;

    /**
     * Convert camelCase to spaced string.
     */
    public static function fromCamelCase(string $string): string;

    /**
     * Set model name from path.
     */
    public static function setModelName(string $model, string $style = 'pascal case'): string;

    /**
     * Set default image settings.
     */
    public static function setDefaultImage(?string $default, string $field, string $model): array;

    /**
     * Convert sidebar array to string.
     */
    public static function convertArraySidebarToString(array $sidebars): string;

    /**
     * Check if menu is active.
     */
    public static function isActiveMenu(string|array $route): string;

    /**
     * Check if generating API.
     */
    public static function isGenerateApi(): bool;

    /**
     * Check if package exists.
     */
    public static function checkPackage(string $name): bool;

    /**
     * Check package version.
     */
    public static function checkPackageVersion(string $name, bool $strict = false): string;

    /**
     * Get composer package info.
     */
    public static function getComposerPackage(string $name): string;

    /**
     * Set disk code for controller.
     */
    public static function setDiskCodeForController(string $name): string;

    /**
     * Set disk code for image cast.
     */
    public static function setDiskCodeForCastImage(string $model, string $field): string;
}
