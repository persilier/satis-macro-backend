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

/**
 * Claim Categories
 */
Route::resource('claim-categories', 'ClaimCategory\ClaimCategoryController', ['except' => ['create']]);

/**
 * Claim Objects
 */
Route::resource('claim-objects', 'ClaimObject\ClaimObjectController');

