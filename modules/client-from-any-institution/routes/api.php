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
 * Clients
 */
Route::prefix('/any')->name('any.')->group(function () {
    Route::resource('clients', 'Clients\ClientController');
    Route::resource('identites.clients', 'Identites\IdentiteClientController', ['only' => ['store']]);
    Route::resource('accounts.clients', 'Accounts\AccountClientController', ['only' => ['store']]);
});

