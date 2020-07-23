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

Route::prefix('my')->group(function () {
    /**
     * Staff
     */
    Route::name('my.')->group(function () {
        Route::resource('staff', 'Staff\StaffController');
        Route::resource('identites.staff', 'Identite\IdentiteStaffController', ['only' => ['store']]);
        // Route for import excel data to database.
        Route::post('import-staffs', 'ImportExport\ImportController@importStaffs');
    });
});