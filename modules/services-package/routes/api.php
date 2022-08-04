<?php
use Illuminate\Support\Facades\Route;
use Satis2020\ServicePackage\Http\Controllers\SatisYearController;

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

Route::get('satis-years',[SatisYearController::class,"index"])->name('satis.years');