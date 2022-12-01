<?php

use Illuminate\Support\Facades\Route;
use Zzzul\Generator\Http\Controllers\GeneratorController;
use Zzzul\Generator\Http\Controllers\SimpleGeneratorController;
use Zzzul\Generator\Http\Middleware\GeneratorOnlyWorkOnLocal;

Route::middleware(['web', GeneratorOnlyWorkOnLocal::class])->group(function () {
    Route::get('/generators/get-sidebar-menus/{index}', [GeneratorController::class, 'getSidebarMenus'])
        ->name('generators.get-sidebar-menus');
        
    Route::resource('simple-generators', SimpleGeneratorController::class)->only('create', 'store');

    Route::resource('generators', GeneratorController::class)->only('create', 'store');
});

