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
     * Convert string to singular pascal case.
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
     * Convert string to plural pascal case.
     */
    public static function pluralCamelCase(string $string): string;

    /**
     * Convert string to singular pascal case.
     */
    public static function singularCamelCase(string $string): string;

    /**
     * Convert string to plural, kebab case, and lowercase.
     */
    public static function pluralKebabCase(string $string): string;

    /**
     * Convert string to kebab case, and lowercase.
     */
    public static function kebabCase(string $string): string;

    /**
     * Convert string to singular, kebab case, and lowercase.
     */
    public static function singularKebabCase(string $string): string;

    /**
     * Convert string to singular, remove special characters, and lowercase.
     */
    public static function cleanSingularLowerCase(string $string): string;

    /**
     * Remove special characters, and lowercase.
     */
    public static function cleanLowerCase(string $string): string;

    /**
     * Convert string to plural, remove special characters, and uppercase every first letters.
     */
    public static function cleanPluralUcWords(string $string): string;

    /**
     * Convert string to singular, remove special characters, and uppercase every first letters.
     */
    public static function cleanSingularUcWords(string $string): string;

    /**
     * Remove special characters, and uppercase every first letters.
     */
    public static function cleanUcWords(string $string): string;

    /**
     * Convert string to plural, remove special characters, and lowercase.
     */
    public static function cleanPluralLowerCase(string $string): string;

    /**
     * Get 1 column after id on the table.
     */
    public static function getColumnAfterId(string $table): string;

    /**
     * Select id and column after id on the table.
     */
    public static function selectColumnAfterIdAndIdItself(string $table): string;

    /**
     * Get model location or path if contains '/'.
     */
    public static function getModelLocation(string $model): string;

    /**
     * Converts camelCase string to have spaces between each.
     */
    public static function fromCamelCase(string $string): string;
    /**
     * Set model name from the latest of array(if exists).
     */
    public static function setModelName(string $model, string $style = 'pascal case'): string;

    /**
     * Set default image and code to controller.
     */
    public static function setDefaultImage(null|string $default, string $field, string $model): array;

    /**
     * Convert array from config to string like array.
     */
    public static function convertArraySidebarToString(array $sidebars): string;

    /**
     * Check if menu is active.
     */
    public static function isActiveMenu(string|array $route): string;

    /**
     * Check if generate blade(default) or api.
     */
    public static function isGenerateApi(): bool;
}
