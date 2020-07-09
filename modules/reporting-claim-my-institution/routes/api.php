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
Route::prefix('/my')->name('my.')->group(function () {
    Route::get('/reporting-claim', 'Reporting\ClaimController@index')->name('reporting-claim.index');
});