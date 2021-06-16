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
Route::apiResource('institutions', 'Institutions\InstitutionController')->except(['destroy']);
Route::name('institutions.update.logo')->post('institutions/{institution}/update-logo', 'Institutions\InstitutionController@updateLogo');
Route::resource('institutions.units', 'Institutions\InstitutionUnitController@index', ['only' => ['index']]);

/*
 *  Type Clients, Category Clients
 */
Route::resource('institutions.type-clients', 'Institutions\InstitutionTypeClientController', ['only' => ['index']]);
Route::resource('institutions.category-clients', 'Institutions\InstitutionCategoryClientController', ['only' => ['index']]);
Route::resource('institutions.client-from-my-institution', 'Institutions\InstitutionClientController', ['only' => ['index']]);
Route::get('institutions/logo', 'Institutions\InstitutionController@getImage');
Route::post('institutions/{institution}/update', 'Institutions\InstitutionController@update');
