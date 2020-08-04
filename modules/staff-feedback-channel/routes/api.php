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
 * channels
 */
Route::get('feedback-channels', 'StaffFeedbackChannel\StaffFeedbackChannelController@edit')->name('feedback-channels.edit');
Route::put('feedback-channels', 'StaffFeedbackChannel\StaffFeedbackChannelController@update')->name('feedback-channels.update');
