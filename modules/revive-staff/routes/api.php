<?php

use Illuminate\Support\Facades\Route;
use Satis2020\ReviveStaff\Http\Controllers\ReviveStaff\ReviveStaffController;
use Satis2020\ReviveStaff\Http\Controllers\ReviveStaff\StaffRivivalController;

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
 * channels
 */
Route::post('revive-staff/{claim}', 'ReviveStaff\ReviveStaffController@store')->name('revive.staff.store');
Route::get("revivals",[ReviveStaffController::class,"index"]);
Route::get("revivals/create",[ReviveStaffController::class,"index"]);
Route::get("revivals/staff/{staffId?}",[StaffRivivalController::class,"index"]);