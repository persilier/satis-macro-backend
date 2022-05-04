<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/my')->name('my.')->group(function () {

    Route::post('system-efficiency-report', 'SystemEfficiencyReportController@index')->name('system-efficiency-rapport');

});