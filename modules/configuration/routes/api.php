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

// Update Coef send relance
Route::get("/configurations/relance", "Relance\RelanceController@show")->name("configurations.relance.show");
Route::put("/configurations/relance", "Relance\RelanceController@update")->name("configurations.relance.update");

// Qualifications parameters
Route::resource('delai-qualification-parameters', 'DelaiParameters\QualificationController')->except(['edit', 'update']);
// Treatments parameters
Route::resource('delai-treatment-parameters', 'DelaiParameters\TreatmentController')->except(['edit', 'update']);

// Component configurations
Route::resource('components', 'Component\ComponentController')->except(['create', 'edit']);
Route::get('components/retrieve-by-name/{componentName}', 'Component\ComponentController@showByName')->name('components.show.by.name');

// Recurrence Alert configurations
Route::get("/configurations/recurrence-alert", "RecurrenceAlert\RecurrenceAlertController@show")->name("configurations.recurrence.alert.show");
Route::put("/configurations/recurrence-alert", "RecurrenceAlert\RecurrenceAlertController@update")->name("configurations.recurrence.alert.update");