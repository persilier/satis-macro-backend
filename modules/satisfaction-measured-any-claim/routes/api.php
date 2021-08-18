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
 * ClaimSatisfactionMeasured
 */
Route::prefix('/any')->name('any.')->group(function () {

    Route::get('/claim-satisfaction-measured', 'ClaimSatisfactionMeasured\ClaimSatisfactionMeasuredController@index')->name('claim.satisfaction.treatment.measured');
    Route::get('/claim-satisfaction-measured/{claim}', 'ClaimSatisfactionMeasured\ClaimSatisfactionMeasuredController@show')->name('claim.satisfaction.measured.show');
    Route::put('/claim-satisfaction-measured/{claim}', 'ClaimSatisfactionMeasured\ClaimSatisfactionMeasuredController@satisfactionMeasured')->name('claim.satisfaction.measured.measured');

});