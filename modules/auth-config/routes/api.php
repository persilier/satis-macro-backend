<?php

use Illuminate\Support\Facades\Route;
use Satis2020\AuthConfig\Http\Controllers\AuthConfigController;

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
 * Models
 */

Route::get('auth-config', [AuthConfigController::class,"show"])->name('auth.config.show');
Route::put('auth-config',  [AuthConfigController::class,"update"])->name('auth.config.update');