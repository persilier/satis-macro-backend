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

Route::prefix('without-client')->group(function () {
    /**
     * Staff
     */
    Route::name('without-client.')->group(function () {
        Route::resource('claims', 'Claim\ClaimController')->only(['create', 'store']);
        Route::resource('identites.claims', 'Identite\IdentiteClaimController', ['only' => ['store']]);
        Route::post('import-claim', 'ImportExport\ImportController@importClaims');
    });
});
