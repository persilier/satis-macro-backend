<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/my')->name('my.')->group(function () {

    Route::post('global-rapport', 'GlobalReportController@index')->name('global-rapport');
    Route::get('units', 'GlobalReportController@create')->name('units');

});