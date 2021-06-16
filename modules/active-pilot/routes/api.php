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
 * Active Pilot
 */
Route::get('active-pilot/institutions/{institution}', 'ActivePilot\ActivePilotController@edit')->name('edit.active.pilot');
Route::put('active-pilot/institutions/{institution}', 'ActivePilot\ActivePilotController@update')->name('update.active.pilot');
