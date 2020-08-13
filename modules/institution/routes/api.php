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
*/

/*
 * Institutions
 */
/*
Route::resource('institutions', 'Institutions\InstitutionController')->except(['create', 'edit']);*/
Route::name('institutions.update.logo')->post('institutions/{institution}/update-logo', 'Institutions\InstitutionController@updateLogo');
Route::resource('institutions.units', 'Institutions\InstitutionUnitController')->only(['index']);
Route::resource('institutions.clients', 'Institutions\InstitutionClientController')->only(['index']);

Route::prefix('any')->name('any.')->group(function () {

    Route::get('institutions/{institution}/message-apis/create', 'Institutions\InstitutionMessageApiController@create')->name('institutions.message-apis.create');

    Route::post('institutions/{institution}/message-apis', 'Institutions\InstitutionMessageApiController@store')->name('institutions.message-apis.store');

});

Route::prefix('my')->name('my.')->group(function () {

    Route::get('institutions/message-apis/create', 'Institutions\MyInstitutionMessageApiController@create')->name('institutions.message-apis.create');

    Route::post('institutions/message-apis', 'Institutions\MyInstitutionMessageApiController@store')->name('institutions.message-apis.store');

});