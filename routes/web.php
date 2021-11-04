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

Route::get('mail', 'Controllers@index');
Route::get('download/{file}', 'Controllers@download');
Route::get('download-uemoa-reports/{file}', 'Controllers@downloadExcelReports');
Route::get('download-excel/{file}', 'Controllers@downloadExcels');
Route::get('new-claim-reference/{institution}', 'Controllers@claimReference');
