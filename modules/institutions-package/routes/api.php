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
Route::apiResource('institutions', 'Institutions\InstitutionController');
Route::name('institutions.update.logo')->post('institutions/{institution}/update-logo', 'Institutions\InstitutionController@updateLogo');

/*
 *  Type Clients, Category Clients
 */
Route::resource('institutions.type-clients', 'Institutions\InstitutionTypeClientController', ['only' => ['index']]);
Route::resource('institutions.category-clients', 'Institutions\InstitutionCategoryClientController', ['only' => ['index']]);