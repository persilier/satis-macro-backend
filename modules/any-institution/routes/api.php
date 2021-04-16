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

/*
 * Institutions
 */

Route::prefix('/any')->name('any.')->group(function () {
    Route::resource('institutions', 'Institutions\InstitutionController');
    Route::name('institutions.update.logo')->post('institutions/{institution}/update-logo', 'Institutions\InstitutionController@updateLogo');
    Route::resource('institutions.units', 'Institutions\InstitutionUnitController')->only(['index']);

    Route::post('import-institutions', 'ImportExport\ImportController@importInstitutions');
});
