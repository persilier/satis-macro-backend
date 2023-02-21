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
 * currenies
 */
Route::get('prediction-ai-institution-category-object', "PredictionAi\PredictionAiController@index");
Route::get('prediction-ai-institution-claim', "PredictionAi\PredictionAiController@indexInstitutionClaim");
Route::get('prediction-ai-institution-treated-unit-object', "PredictionAi\PredictionAiController@indexInstitutionTUnitTreatedClaimWithObject");
Route::get('prediction-ai-institution-treated-claim', "PredictionAi\PredictionAiController@indexInstitutionClaimTreated");
