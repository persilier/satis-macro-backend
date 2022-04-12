<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/my')->name('my.')->group(function () {

    Route::post('benchmarking-rapport', 'BenchmarkingReportController@index')->name('benchmarking-rapport');

});