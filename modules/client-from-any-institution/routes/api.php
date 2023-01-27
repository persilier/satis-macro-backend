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

    Route::resource('clients', 'Clients\ClientController', ['except' => ['show']]);
    Route::post('clients/{client_id}', 'Clients\ClientController@show');
    Route::resource('clients.institutions', 'Clients\ClientInstitutionController', ['only' => ['index']]);
    Route::resource('identites.clients', 'Identites\IdentiteClientController', ['only' => ['store']]);
    Route::resource('accounts.clients', 'Accounts\AccountClientController', ['only' => ['store']]);
    // Route for import excel data to database.
    Route::post('import-clients', 'ImportExport\ImportController@importClients');
});

