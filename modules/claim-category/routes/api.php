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

/*
 * ClaimCategories
 */
Route::resource('claim-categories', 'ClaimCategories\ClaimCategoryController')->except(['create', 'edit']);
Route::resource('claim-categories.claim-objects', 'ClaimCategories\ClaimCategoryObjectController')->only(['index']);
// Route for import excel data to database.
Route::post('import-claim-categories', 'ImportExport\ImportController@importClaimCategories');
