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
Route::get('notifications/edit', 'Notification\NotificationController@edit')->name('notifications.edit');
Route::put('notifications', 'Notification\NotificationController@update')->name('notifications.update');

Route::get('unread-notifications', 'Notification\UnreadNotificationController@index')->name('notifications.unread.index');
Route::put('unread-notifications', 'Notification\UnreadNotificationController@update')->name('notifications.unread.update');

