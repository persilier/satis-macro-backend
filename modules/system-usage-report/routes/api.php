<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/my')->name('my.')->group(function () {

    Route::post('system-usage-rapport', 'SystemUsageReportController@index')->name('system-usage-rapport');

});