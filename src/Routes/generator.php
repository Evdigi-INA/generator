<?php

use EvdigiIna\Generator\Http\Controllers\GeneratorController;
use EvdigiIna\Generator\Http\Controllers\SimpleGeneratorController;
use EvdigiIna\Generator\Http\Middleware\OnlyAvailableInTheFullVersion;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', TheGeneratorOnlyWorksInTheLocalEnv::class])->group(function () {
    Route::get('/generators/get-sidebar-menus/{index}', [GeneratorController::class, 'getSidebarMenus'])
        ->name('generators.get-sidebar-menus');

    Route::resource('simple-generators', SimpleGeneratorController::class)
        ->only('create', 'store');

    Route::resource('generators', GeneratorController::class)
        ->only('create', 'store')
        ->middleware(OnlyAvailableInTheFullVersion::class);
});
