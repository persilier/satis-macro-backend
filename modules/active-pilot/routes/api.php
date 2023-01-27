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

Route::get('configuration-active-pilot', 'ConfigurationPilot\ConfigurationPilotController@index')->name('config.active.pilot');
Route::post('configuration-active-pilot', 'ConfigurationPilot\ConfigurationPilotController@store')->name('config.active.pilot');

Route::post('pilot-relaunch-other', 'RelanceByPilot\RelanceByPilotController@store')->name('relaunch.other');

Route::post('reassignment-to-pilot', 'ReassignmentToPilot\ReassignmentToPilotController@store')->name('reassignment.store');
