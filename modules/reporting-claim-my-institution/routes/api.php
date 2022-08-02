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
 * Monitoring
 */
Route::prefix('/my')->name('my.')->group(function () {
    Route::get('/reporting-claim', 'Reporting\ClaimController@index')->name('reporting-claim.index');

    // Export pdf
    Route::post('/reporting-claim/export-pdf', 'Export\ExportController@pdfExport')->name('reporting-claim.pdfExport');

    // Configurations exports pdf auto
    Route::get('/reporting-claim/config', 'Config\ReportingTasksController@index')->name('reporting-claim.config.index');
    Route::get('/reporting-claim/config/create', 'Config\ReportingTasksController@create')->name('reporting-claim.config.create');
    Route::get('/reporting-claim/config/{reportingTask}/edit', 'Config\ReportingTasksController@edit')->name('reporting-claim.config.edit');
    Route::post('/reporting-claim/config', 'Config\ReportingTasksController@store')->name('reporting-claim.config.store');
    Route::put('/reporting-claim/config/{reportingTask}', 'Config\ReportingTasksController@update')->name('reporting-claim.config.update');
    Route::delete('/reporting-claim/config/{reportingTask}', 'Config\ReportingTasksController@destroy')->name('reporting-claim.config.destroy');


    //regulatory state reporting
    Route::post("/reporting-claim/regulatory-state","Reporting\RegulatoryState\RegulatoryStateReportingController@index")->name("reporting-claim.regulatory-state");
});