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
 * Update Claim against my institution
 */

Route::prefix('/my')->name('my.')->group(function () {
    Route::resource('claims-incompletes', 'Claims\ClaimController')->except(['create', 'store']);
});
