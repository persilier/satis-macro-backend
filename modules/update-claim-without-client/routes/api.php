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
 * Update Claim against any institution
 */

Route::prefix('/without-client')->name('without-client.')->group(function () {
    Route::resource('claims-incompletes', 'Claims\ClaimController')->except(['create', 'store']);
});
