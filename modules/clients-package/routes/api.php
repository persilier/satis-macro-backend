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
 * Type clients et Catégory clients
 * Clients
 */
Route::apiResource('type-clients', 'TypeClients\TypeClientController');
Route::apiResource('category-clients', 'CategoryClients\CategoryClientController');
Route::apiResource('clients', 'Clients\ClientIdentiteController');
Route::resource('clients.create', 'Clients\ClientController', ['only' => ['store']]);
