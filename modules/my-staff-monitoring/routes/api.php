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
 * My Monitoring
 */
Route::prefix('/my')->name('my.')->group(function () {
    Route::post('/monitoring-by-staff', 'MyStaffMonitoringController@index')->name('monitoring-by-staff.index');
    Route::get('/unit-staff', 'MyStaffMonitoringController@show')->name('unit-staff.show');
});