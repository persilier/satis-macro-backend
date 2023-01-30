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
|*/
Route::prefix('/any')->name('any.')->group(function () {

    Route::get('/claim-archived', 'ClaimArchived\ClaimArchivedController@index')->name('claim.archived.index');
    Route::get('/claim-archived/{claim}', 'ClaimArchived\ClaimArchivedController@show')->name('claim.archived.show');

});
