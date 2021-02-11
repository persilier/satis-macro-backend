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
Route::prefix('/any')->name('any.')->group(function () {

    Route::get('/uemoa/institution', 'Institution\InstitutionController@index')->name('uemoa-institution.index');

    Route::get('/uemoa/global-state-report', 'GlobalStateReport\GlobalStateReportController@index')->name('uemoa-global-state-report.index');
    Route::post('/uemoa/global-state-report', 'GlobalStateReport\GlobalStateReportController@excelExport')->name('uemoa-global-state-report.excelExport');

    Route::get('/uemoa/state-more-30-days', 'StateMore30Days\StateMore30DaysController@index')->name('uemoa-out-time-30-days.index');
    Route::post('/uemoa/state-more-30-days', 'StateMore30Days\StateMore30DaysController@excelExport')->name('uemoa-out-time-30-days.excelExport');

    Route::get('/uemoa/state-out-time', 'StateOutTime\StateOutTimeController@index')->name('uemoa-state-out-time.index');
    Route::post('/uemoa/state-out-time', 'StateOutTime\StateOutTimeController@excelExport')->name('uemoa-state-out-time.excelExport');

    Route::get('/uemoa/state-analytique', 'StateAnalytique\StateAnalytiqueController@index')->name('uemoa-state-analytique.index');
    Route::post('/uemoa/state-analytique', 'StateAnalytique\StateAnalytiqueController@excelExport')->name('uemoa-state-analytique.excelExport');

});
