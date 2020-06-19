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
Route::prefix('/any')->name('any.')->group(function () {
    Route::get('/processing-circuits', 'ProcessingCircuitAnyInstitutions\ProcessingCircuitAnyInstitutionController@index')->name('processing-circuits.index');
    Route::get('/processing-circuits/{institution}', 'ProcessingCircuitAnyInstitutions\ProcessingCircuitAnyInstitutionController@edit')->name('processing-circuits.edit');
    Route::put('/processing-circuits/{institution}', 'ProcessingCircuitAnyInstitutions\ProcessingCircuitAnyInstitutionController@update')->name('processing-circuits.update');
});