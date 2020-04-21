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
 * faq
 */
Route::apiResource('faq-categories', 'FaqCategory\FaqCategoryController');
Route::apiResource('faqs', 'Faq\FaqController');
Route::resource('faq-categories.faqs', 'FaqCategory\FaqCategoryFaqController', ['only' => ['index']]);


