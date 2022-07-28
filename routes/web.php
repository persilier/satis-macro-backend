<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('mail', 'Controller@index');
Route::get('download/{file}', 'Controller@download');
Route::get('download-uemoa-reports/{file}', 'Controller@downloadExcelReports');
Route::get('download-excel/{file}', 'Controller@downloadExcels');
Route::get('new-claim-reference/{institution}', 'Controller@claimReference');

