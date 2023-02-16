<?php

use Illuminate\Support\Facades\Route;
use EvdigiIna\Generator\Http\Controllers\GeneratorController;
use EvdigiIna\Generator\Http\Middleware\GeneratorOnlyWorkOnLocal;

Route::middleware(['web', TheGeneratorOnlyWorksInTheLocalEnv::class])->group(function () {
    Route::get('/generators/get-sidebar-menus/{index}', [GeneratorController::class, 'getSidebarMenus'])
        ->name('generators.get-sidebar-menus');

    Route::resource('simple-generators', SimpleGeneratorController::class)
        ->only('create', 'store');

    Route::resource('generators', GeneratorController::class)
        ->only('create', 'store')
        ->middleware(OnlyAvailableInTheFullVersion::class);
});
