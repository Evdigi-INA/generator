<?php

use EvdigiIna\Generator\Http\Controllers\GeneratorController;
use EvdigiIna\Generator\Http\Controllers\SimpleGeneratorController;
use EvdigiIna\Generator\Http\Middleware\AlreadyInstallApi;
use EvdigiIna\Generator\Http\Middleware\OnlyAvailableInTheFullVersion;
use EvdigiIna\Generator\Http\Middleware\TheGeneratorOnlyWorksInTheLocalEnv;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', TheGeneratorOnlyWorksInTheLocalEnv::class])->group(function () {
    Route::get('/generators/get-sidebar-menus/{index}', [GeneratorController::class, 'getSidebarMenus'])
        ->name('generators.get-sidebar-menus');

    Route::resource('simple-generators', SimpleGeneratorController::class)
        ->only('create', 'store');

    Route::get('/api-generators/create', [GeneratorController::class, 'apiCreate'])
        ->middleware(AlreadyInstallApi::class);

    Route::post('/api-generators', [GeneratorController::class, 'store'])
        ->middleware(AlreadyInstallApi::class);

    Route::resource('generators', GeneratorController::class)
        ->only('create', 'store')
        ->middleware(OnlyAvailableInTheFullVersion::class);
});
