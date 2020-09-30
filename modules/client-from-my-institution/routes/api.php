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
Route::prefix('/my')->name('my.')->group(function () {
    Route::resource('clients', 'Clients\ClientController');
    Route::resource('identites.clients', 'Identites\IdentiteClientController', ['only' => ['store']]);
    Route::resource('accounts.clients', 'Accounts\AccountClientController', ['only' => ['store']]);
    // Route for import excel data to database.
    Route::get('import-clients', 'ImportExport\ImportController@downloadFile');
    Route::post('import-clients', 'ImportExport\ImportController@importClients');
});

