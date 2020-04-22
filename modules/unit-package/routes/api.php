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
 * Unit Types
 */
Route::resource('unit-types', 'UnitType\UnitTypeController', ['except' => ['create', 'edit']]);

/**
 * Units
 */
Route::resource('units', 'Unit\UnitController');