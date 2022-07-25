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
Route::prefix('/useful-data')->name('backoffice.')->group(function () {
    Route::get('/claim/create', 'RetrieveDataForCreateClaim\RetrieveDataController@create')->name('claim.create');
});