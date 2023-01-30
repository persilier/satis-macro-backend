<?php

use Illuminate\Support\Facades\Route;
use Satis2020\Escalation\Http\Controllers\Config\EscalationConfigController;
use Satis2020\Escalation\Http\Controllers\TreatmentBoard\TreatmentBoardController;

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

Route::get('escalation-config', [EscalationConfigController::class,"show"])->name('escalation.config.show');
Route::put('escalation-config',  [EscalationConfigController::class,"update"])->name('escalation.config.update');
Route::resource('treatments-boards',  TreatmentBoardController::class);