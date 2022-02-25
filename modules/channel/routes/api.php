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
Route::apiResource('channels', 'Channels\ChannelController');
Route::get('response-channels', 'Channels\ResponseChannelController@index')->name('response.channel.index');
Route::get('mobile-channel', 'Channels\MobileChannelController@index')->name('mobile.channel.index');
Route::put(
    'channels/{channel}/toggle-is-response',
    'Channels\ChannelToggleIsResponseController@update'
)->name('channels.toggle-is-response.update');
