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

Route::prefix('portal/my')->group(function () {
    /**
     * Staff
     */
    Route::name('portal.my.')->group(function () {
        Route::get('claims/{institution}/create', 'Claim\ClaimController@create')->name('create');
        Route::post('claims', 'Claim\ClaimController@store')->name('store');
        Route::resource('identites.claims', 'Identite\IdentiteClaimController', ['only' => ['store']]);

        Route::post('others-claims-to-satis', 'Claim\OthersClaimsController@store')->name('store');
    });

});
