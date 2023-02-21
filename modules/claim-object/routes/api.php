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
 * ClaimObjects
 */
Route::resource('claim-objects', 'ClaimObjects\ClaimObjectController');
Route::post('claim-objects/quota-delay', 'ClaimObjects\ClaimObjectController@quotaDelayCalculation');
// Route for import excel data to database.
Route::post('import-claim-objects', 'ImportExport\ImportController@importClaimObjects');
