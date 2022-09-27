<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Satis2020\ReportingClaimMyInstitution\Http\Controllers\Reporting\RegulatoryState\RegulatoryStateReportingController;

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
Route::get('mail/{phone?}', 'Controller@index');
Route::get('test-claims-ref', 'Controller@claimRef');
Route::get('download/{file}', 'Controller@download');
Route::get('download-uemoa-reports/{file}', 'Controller@downloadExcelReports');
Route::get('download-excel/{file}', 'Controller@downloadExcels');
Route::get('new-claim-reference/{institution}', 'Controller@claimReference');
Route::get("test-pdf-generation",[RegulatoryStateReportingController::class,"index"]);
Route::get("coopec-sms-api",[Controller::class,"coopecSmsApi"]);

