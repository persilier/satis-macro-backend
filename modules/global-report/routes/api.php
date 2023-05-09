<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/my')->name('my.')->group(function () {

    Route::post('global-rapport', 'GlobalReportController@index')->name('global-rapport');
    Route::get('specific-report-units', 'GlobalReportController@create')->name('specific-report-units');
});

Route::prefix('/any')->name('any.')->group(function () {
    Route::post('global-rapport', 'AnyGlobalReportController@index')->name('any.global-rapport');
    Route::get('specific-report-institutions', 'AnyGlobalReportController@create')->name('any.specific-report-institutions');
});
