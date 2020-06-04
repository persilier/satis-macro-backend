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
    Route::get('/institutions', 'Institutions\InstitutionController@getMyInstitution');
    Route::put('/institutions', 'Institutions\InstitutionController@updateMyInstitution');
    Route::post('/institutions/logo', 'Institutions\InstitutionController@updateLogo');


    Route::get('/institutions/units', 'Institutions\InstitutionUnitController@index');
});
