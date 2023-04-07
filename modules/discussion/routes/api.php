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
 * discussions
 */
Route::apiResource('discussions', 'Discussion\DiscussionController')->only(['index', 'store','create', 'destroy']);

Route::apiResource('discussions.staff', 'Discussion\DiscussionStaffController')->only(['index', 'store', 'destroy', 'create']);

Route::apiResource('discussions.messages', 'Discussion\DiscussionMessageController')->only(['index', 'store', 'destroy']);
