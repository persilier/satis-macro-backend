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
 * Update sms parameters
 */
Route::get("/configurations/sms", "Sms\SmsController@show")->name("configurations.sms.show");
Route::put("/configurations/sms", "Sms\SmsController@update")->name("configurations.sms.update");

/**
 * Update mail parameters
 */
Route::get("/configurations/mail", "Mail\MailController@show")->name("configurations.mail.show");
Route::put("/configurations/mail", "Mail\MailController@update")->name("configurations.mail.update");