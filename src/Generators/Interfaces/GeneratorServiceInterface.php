<?php

namespace EvdigiIna\Generator\Generators\Interfaces;

interface GeneratorServiceInterface
{
    /**
     * Generate all CRUD modules.
     */
    public function generateAll(array $request): void;

    /**
     * Generate only model and migration.
     */
    public function onlyGenerateModelAndMigration(array $request): void;

    /**
     * Simple generator, only generate the core module(CRUD).
     */
    public function simpleGenerator(array $request): void;


    /**
     * Get sidebar menus by index.
     */
    public function getSidebarMenusByIndex(int $index): array;

    /**
     * Check sidebar view.
     */
    public function checkSidebarType(): void;

    /**
     * Check to see if any files are the same as the generated files. (will be used in the future)
     * */
    public function checkFilesAreSame(array $request): array;
}
