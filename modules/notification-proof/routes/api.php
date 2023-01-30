<?php

use Illuminate\Support\Facades\Route;
use Satis2020\AuthConfig\Http\Controllers\AuthConfigController;
use Satis2020\NotificationProof\Http\Controllers\ExportToPdfIndependantNotificationProofController;
use Satis2020\NotificationProof\Http\Controllers\IndependantNotificationProofController;
use Satis2020\NotificationProof\Http\Controllers\NotificationProofController;

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

Route::post('my/notifications/proofs/{pagination?}', [IndependantNotificationProofController::class,"index"])->name('my.notif.proof.index');
Route::post('my/export/notifications/proofs/', [ExportToPdfIndependantNotificationProofController::class,"index"])->name('my.notif.proof.index');

Route::post('notifications/proofs/{pagination?}',  [NotificationProofController::class,"index"])->name('notif.proof.index');
Route::post('any/notifications/proofs/{pagination?}',  [NotificationProofController::class,"index"])->name('notif.proof.index');

Route::get('notifications/proofs/create',  [NotificationProofController::class,"create"])->name('notif.proof.create');

