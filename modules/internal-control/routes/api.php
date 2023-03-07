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
*/
/*
 *
 * currenies
 */

Route::get('configuration-internal-control', 'InternalControl\InternalControlController@index');
Route::post('configuration-internal-control', 'InternalControl\InternalControlController@store');
Route::post('claims-internal-control', 'InternalControl\InternalControlController@indexClaimsInternalControl');
Route::get('claim-objects-internal-control', 'InternalControl\InternalControlController@indexClaimObject');
Route::get('claim-detail-internal-control/{id}', 'InternalControl\InternalControlController@show');
