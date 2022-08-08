<?php

use Illuminate\Support\Facades\Route;
use Satis2020\Webhooks\Http\Controllers\Config\WebhooksConfigController;

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

Route::apiResource('webhooks',  WebhooksConfigController::class)->except(['show','edit','create']);
Route::get('webhooks/{webhook}/edit',[WebhooksConfigController::class,"edit"])->name('webhooks.edit');
Route::get('webhooks/create',[WebhooksConfigController::class,"create"])->name('webhooks.create');