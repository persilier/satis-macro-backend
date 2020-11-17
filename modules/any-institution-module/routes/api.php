<?php

use Illuminate\Support\Facades\Route;


Route::prefix('/any')->name('any.')->group(function () {
    /**
     * Modules
     */
    Route::resource('module', 'AnyInstitutionModule\AnyInstitutionModuleController');
});
