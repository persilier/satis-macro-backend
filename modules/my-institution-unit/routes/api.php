<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|

/**
 * Units
 */

Route::prefix('/my')->name('my.')->group(function () {
    Route::resource('units', 'Unit\UnitController');

    Route::post('/import-unit-type-unit', 'ImportExport\ImportController@importUnitTypeUnit');
});