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

Route::get('/claim-awaiting-validation-any-institution', 'AwaitingValidation\AwaitingValidationController@index')->name('claim.awaiting.validation.any.institution.index');
Route::get('/claim-awaiting-validation-any-institution/{claim}', 'AwaitingValidation\AwaitingValidationController@show')->name('claim.awaiting.validation.any.institution.show');
Route::put('/claim-awaiting-validation-any-institution/{claim}/validate', 'AwaitingValidation\AwaitingValidationController@validated')->name('claim.awaiting.validation.any.institution.validate');
Route::put('/claim-awaiting-validation-any-institution/{claim}/invalidate', 'AwaitingValidation\AwaitingValidationController@invalidated')->name('claim.awaiting.validation.any.institution.invalidate');
