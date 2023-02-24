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
Route::prefix('/useful-data-claims-classification')->name('claims-classification.')->group(function () {
    Route::get('/claims', 'Claims\ClaimController@index')->name('claims.index.revoked');
    Route::get('/claims/revoked', 'Claims\ClaimRevokedController@index')->name('claims.index.revoked');
    Route::get('/categories/{name}/objects', 'Claims\ClaimCategoryController@index')->name('categories.index');
    Route::get('/objects/{name}', 'Claims\ClaimObjectController@index')->name('claims.object.index');
});

