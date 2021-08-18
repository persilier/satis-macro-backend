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
 * ClaimObjectRequirement
 */
Route::get('/claim-object-requirements', 'ClaimObjectRequirement\ClaimObjectRequirementController@edit')->name('claim_object_requirements.edit');
Route::put('/claim-object-requirements', 'ClaimObjectRequirement\ClaimObjectRequirementController@update')->name('claim_object_requirements.update');
