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
/**
 * Monitoring
 */
Route::prefix('/any')->name('any.')->group(function () {
    Route::get('/monitoring-claim', 'Monitoring\ClaimController@index')->name('monitoring-claim.index');
    Route::get('/monitoring-claim/{claim}', 'Monitoring\ClaimController@show')->name('monitoring-claim.show');
});