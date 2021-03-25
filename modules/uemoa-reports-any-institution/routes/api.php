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

    //Route::get('/uemoa/institution', 'Institution\InstitutionController@index')->name('uemoa-institution.index');
    Route::get('/uemoa/data-filter', 'DataFilter\DataFilterController@index')->name('uemoa-filter.index');

    Route::get('/uemoa/global-state-report', 'GlobalStateReport\GlobalStateReportController@index')->name('uemoa-global-state-report.index');
    Route::post('/uemoa/global-state-report', 'GlobalStateReport\GlobalStateReportController@excelExport')->name('uemoa-global-state-report.excelExport');
    Route::post('/uemoa/global-state-report-pdf', 'GlobalStateReport\GlobalStateReportController@pdfExport')->name('uemoa-global-state-report.pdfExport');

    Route::get('/uemoa/state-more-30-days', 'StateMore30Days\StateMore30DaysController@index')->name('uemoa-out-time-30-days.index');
    Route::post('/uemoa/state-more-30-days', 'StateMore30Days\StateMore30DaysController@excelExport')->name('uemoa-out-time-30-days.excelExport');
    Route::post('/uemoa/state-more-30-days-pdf', 'StateMore30Days\StateMore30DaysController@pdfExport')->name('uemoa-out-time-30-days.pdfExport');

    Route::get('/uemoa/state-out-time', 'StateOutTime\StateOutTimeController@index')->name('uemoa-state-out-time.index');
    Route::post('/uemoa/state-out-time', 'StateOutTime\StateOutTimeController@excelExport')->name('uemoa-state-out-time.excelExport');
    Route::post('/uemoa/state-out-time-pdf', 'StateOutTime\StateOutTimeController@pdfExport')->name('uemoa-state-out-time.pdfExport');

    Route::get('/uemoa/state-analytique', 'StateAnalytique\StateAnalytiqueController@index')->name('uemoa-state-analytique.index');
    Route::post('/uemoa/state-analytique', 'StateAnalytique\StateAnalytiqueController@excelExport')->name('uemoa-state-analytique.excelExport');
    Route::post('/uemoa/state-analytique-pdf', 'StateAnalytique\StateAnalytiqueController@pdfExport')->name('uemoa-state-analytique.pdfExport');

});
