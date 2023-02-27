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

Route::prefix('my')->group(function () {
    /**
     * Staff
     */
    Route::name('my.')->group(function () {
        Route::resource('claims', 'Claim\ClaimController')->only(['create', 'store']);
        Route::post('claims-file-extraction', 'Claim\ClaimController@storeFromFile');
        Route::resource('identites.claims', 'Identite\IdentiteClaimController', ['only' => ['store']]);
        Route::post('import-claim', 'ImportExport\ImportController@importClaims');
        Route::post('claims/moral-entity', 'Claim\ClaimMoralEntityController@store');
        Route::get('claims-category-prediction/{description}', 'Claim\ClaimController@getClaimsCategoryPrediction');
    });

});
