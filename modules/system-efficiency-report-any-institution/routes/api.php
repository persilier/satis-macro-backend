<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/any')->name('any.')->group(function () {

    Route::post('system-efficiency-report', 'SystemEfficiencyReportController@index')->name('system-efficiency-rapport');

});