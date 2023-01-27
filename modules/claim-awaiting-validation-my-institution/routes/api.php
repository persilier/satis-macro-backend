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
 * ClaimAwaitingValidation
 */

Route::get('/claim-awaiting-validation-my-institution', 'AwaitingValidation\AwaitingValidationController@index')->name('claim.awaiting.validation.my.institution.index');
Route::get('/claim-awaiting-validation-my-institution-with-config', 'AwaitingValidation\AwaitingValidationConfigController@index')->name('claim.awaiting.validation.my.institution.index');
Route::get('/claim-transferred-my-institution-with-config', 'AwaitingValidation\AwaitingValidationConfigController@getClaimTransferred');
Route::get('/claim-awaiting-validation-my-institution/{claim}', 'AwaitingValidation\AwaitingValidationController@show')->name('claim.awaiting.validation.my.institution.show');
Route::put('/claim-awaiting-validation-my-institution/{claim}/validate', 'AwaitingValidation\AwaitingValidationController@validated')->name('claim.awaiting.validation.my.institution.validate');
Route::put('/claim-awaiting-validation-my-institution/{claim}/invalidate', 'AwaitingValidation\AwaitingValidationController@invalidated')->name('claim.awaiting.validation.my.institution.invalidate');
