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

Route::post('/any/relance/{claim}', 'Relance\RelanceController@sendAnyRelance')->name('any-send-relance');
Route::post('/my/relance/{claim}', 'Relance\RelanceController@sendMyRelance')->name('my-send-relance');