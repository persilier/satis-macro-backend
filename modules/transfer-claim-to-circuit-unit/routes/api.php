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
 * TransferClaimToTargetedInstitution
 */
Route::put('/transfer-claim-to-circuit-unit/{claim}', 'TransferToCircuitUnit\TransferToCircuitUnitController@update')->name('transfer.claim.to.circuit.unit');
Route::get('/transfer-claim-to-circuit-unit/{claim}', 'TransferToCircuitUnit\TransferToCircuitUnitController@edit')->name('get.claim.circuit.units');

Route::get('/getUnitsPrediction/{description}/{object}','TransferToCircuitUnit\TransferToCircuitUnitController@getUnitsPrediction')->name('get.units.prediction');
Route::post('/getUnitsPrediction','TransferToCircuitUnit\TransferToCircuitUnitController@getUnitsPrediction')->name('get.units.prediction');
