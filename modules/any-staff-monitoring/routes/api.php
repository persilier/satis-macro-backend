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
 * Any Monitoring
 */
Route::prefix('/any')->name('any.')->group(function () {
    Route::post('/monitoring-by-staff', 'AnyStaffMonitoringController@index')->name('monitoring-by-staff.index');
    Route::get('/unit-staff', 'AnyStaffMonitoringController@show')->name('unit-staff.show');
    Route::post('/monitoring-pilote', 'PilotMonitoringController@index')->name('monitoring-by.pilote-index');
    Route::get('/monitoring-pilote', 'PilotMonitoringController@show')->name('monitoring-by.pilote-show');
    Route::post('/pilot-unit', 'PilotUnitController@index')->name('unit.pilote-index');
    Route::get('/pilot-unit', 'PilotUnitController@show')->name('unit.pilote-show');
    Route::get('/collector-pilot', 'CollecteurMonitoringController@show')->name('collector.pilot-show');
    Route::post('/collector-pilot', 'CollecteurMonitoringController@index')->name('collector.pilot-index');
    Route::get('/institutions-whithout-holding', 'AnyStaffMonitoringController@create')->name('institutions.holding.create');
});

