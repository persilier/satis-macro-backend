<?php

use Illuminate\Support\Facades\Route;

Route::name('my.')->group(function () {

    Route::post('total-claims-received', 'SystemUsageReportController@index')->name('total-claims-received');

});