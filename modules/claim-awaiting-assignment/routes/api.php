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
 * ClaimAwaitingAssignment
 */

Route::get('/claim-awaiting-assignment', 'AwaitingAssignment\AwaitingAssignmentController@index')->name('claim.awaiting.assignment.index');
Route::get('/claim-awaiting-assignment/{claim}', 'AwaitingAssignment\AwaitingAssignmentController@show')->name('claim.awaiting.assignment.show');
Route::put('/claim-awaiting-assignment/{claim}/merge/{duplicate}', 'AwaitingAssignment\AwaitingAssignmentController@merge')->name('claim.awaiting.assignment.merge');
