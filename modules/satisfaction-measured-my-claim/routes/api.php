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
|*/

Route::prefix('/my')->name('my.')->group(function () {

    /*
    * ClaimSatisfactionMeasured
    */
    Route::get('/claim-satisfaction-measured', 'ClaimSatisfactionMeasured\ClaimSatisfactionMeasuredController@index')->name('claim.satisfaction.treatment.measured');
    Route::get('/claim-satisfaction-measured/{claim}', 'ClaimSatisfactionMeasured\ClaimSatisfactionMeasuredController@show')->name('claim.satisfaction.measured.show');
    Route::put('/claim-satisfaction-measured/{claim}', 'ClaimSatisfactionMeasured\ClaimSatisfactionMeasuredController@satisfactionMeasured')->name('claim.satisfaction.measured.measured');
    Route::get('/claim-unsatisfied', 'ClaimSatisfactionMeasured\UnsatisfiedClaimController@index')->name('claim.unsatisfied.index');
    Route::put('/claim-unsatisfied/close/{claim}', 'ClaimSatisfactionMeasured\ClosedClaimMeasuredController@update')->name('claim.unsatisfied.index');

    Route::get('/staff-claim-for-satisfaction-measured', 'ClaimSatisfactionMeasured\StaffClaimSatisfactionMeasuredController@index')->name('staff.claim.satisfaction.measured');
    Route::get('/staff-claim-for-satisfaction-measured/{claim}', 'ClaimSatisfactionMeasured\StaffClaimSatisfactionMeasuredController@show')->name('staff.claim.satisfaction.measured.show');
    Route::get('/staff-claim-for-satisfaction/measured/create', 'ClaimSatisfactionMeasured\StaffClaimSatisfactionMeasuredController@create')->name('staff.claim.satisfaction.measured.create');
    Route::post('/staff-claim-for-satisfaction-measured/auto-affect', 'ClaimSatisfactionMeasured\StaffClaimSatisfactionMeasuredController@autoAffectForSatisfactionMeasure')->name('staff.claim.satisfaction.measured.auto.affect');
    Route::post('/staff-claim-for-satisfaction-measured/affect', 'ClaimSatisfactionMeasured\StaffClaimSatisfactionMeasuredController@affectForSatisfactionMeasure')->name('staff.claim.satisfaction.measured.affect');
});
