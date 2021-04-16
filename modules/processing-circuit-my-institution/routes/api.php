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

/*
 * Processing Circuit
 */
Route::prefix('/my')->name('my.')->group(function () {
    Route::get('/processing-circuits/', 'ProcessingCircuitMyInstitutions\ProcessingCircuitMyInstitutionController@edit')->name('processing-circuits.edit');
    Route::put('/processing-circuits/', 'ProcessingCircuitMyInstitutions\ProcessingCircuitMyInstitutionController@update')->name('processing-circuits.update');
});
