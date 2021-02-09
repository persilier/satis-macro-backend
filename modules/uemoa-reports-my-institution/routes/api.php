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
 * Gloal State Report Excel
 */
Route::prefix('/my')->name('my.')->group(function () {

    Route::get('/uemoa/global-state-report', 'GlobalStateReport\GlobalStateReportController@index')->name('uemoa-global-state-report.index');

    // Export excel
    Route::post('/uemoa/global-state-report', 'Export\GlobalStateReportController@excelExport')->name('uemoa-global-state-report.excelExport');

});
