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
Route::put("/configurations/limit", "RegulatoryLimit\RegulatoryLimitController@update")->name("configurations.limit.update");

// measure preventive
Route::get("/configurations/measure-preventive", "MeasurePreventive\MeasurePreventiveController@show")->name("configurations.measure.preventive.show");
Route::put("/configurations/measure-preventive", "MeasurePreventive\MeasurePreventiveController@update")->name("configurations.measure.preventive.update");

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

// Reject Unit Transfer Limitation configurations
Route::get("/configurations/reject-unit-transfer-limitation", "RejectUnitTransferLimitation\RejectUnitTransferLimitationController@show")->name("configurations.reject.unit.transfer.limitation.show");
Route::put("/configurations/reject-unit-transfer-limitation", "RejectUnitTransferLimitation\RejectUnitTransferLimitationController@update")->name("configurations.reject.unit.transfer.limitation.update");

// Min Fusion Percent configurations
Route::get("/configurations/min-fusion-percent", "MinFusionPercent\MinFusionPercentController@show")->name("configurations.min.fusion.percent.limitation.show");
Route::put("/configurations/min-fusion-percent", "MinFusionPercent\MinFusionPercentController@update")->name("configurations.min.fusion.percent.limitation.update");

// Pilot and Collector attribute in discussions configurations

Route::get("/configurations/allow-pilot-collector-discussion", "Discussion\AllowPilotCollectorToDiscussionController@show")->name("configurations.allow.pilot.collector.discussion.show");
Route::put("/configurations/allow-pilot-collector-discussion", "Discussion\AllowPilotCollectorToDiscussionController@update")->name("configurations.allow.pilot.collector.discussion.update");

// configuration of the delay quota of treatment for each object  reclamation

Route::get("/quota-delay/treatment", "QuotaDelay\QuotaDelayController@show")->name("quota.delay.treatment.show");
Route::put("/quota-delay/treatment", "QuotaDelay\QuotaDelayController@update")->name("quota.delay.treatment.update");

// Configurations pour les l'integration des donnes de la mesure de satisfaction
Route::get("/configurations/satisfaction-data-config", "SatisfactionData\SatisfactionDataController@show")->name("satisfaction.data.config.show");
Route::put("/configurations/satisfaction-data-config", "SatisfactionData\SatisfactionDataController@update")->name("satisfaction.data.config.update");
