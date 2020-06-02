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
Route::prefix('/my')->name('my.')->group(function () {
    Route::resource('institutions', 'Institutions\InstitutionController')->only(['show', 'update']);
    Route::name('institutions.update.logo')->post('institutions/{institution}/update-logo', 'Institutions\InstitutionController@updateLogo');
    Route::resource('institutions.units', 'Institutions\InstitutionUnitController')->only(['index']);
});
/*