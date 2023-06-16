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

Route::prefix('any')->group(function () {
    /**
     * Staff
     */
    Route::name('any.')->group(function () {
        Route::resource('claims', 'Claim\ClaimController')->only(['create', 'store']);
        Route::resource('identites.claims', 'Identite\IdentiteClaimController', ['only' => ['store']]);
        Route::resource('identites-moral.claims', 'Identite\MoralIdentiteController', ['only' => ['store']]);
        Route::post('import-claim', 'ImportExport\ImportController@importClaims');
        Route::post('claims/moral-entity', 'Claim\ClaimMoralEntityController@store');

    });
});
