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
Route::put('/transfer-claim-to-unit/{claim}', 'TransferToUnit\TransferToUnitController@update')->name('transfer.claim.to.unit');
Route::get('/transfer-claim-to-unit/{claim}', 'TransferToUnit\TransferToUnitController@edit')->name('get.claim.units');
