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
/**
 * Global  Report
 */


    Route::post('/bci-reports/global', 'GlobalReport\GlobalReportController@index')->name('bci-reports.global');
    Route::post('/bci-reports/global-condensed', 'GlobalReport\GlobalCondensedAnnualReportController@index')->name('bci-reports.global-condensed');
