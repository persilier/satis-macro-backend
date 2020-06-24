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
 * ClaimAwaitingTreatment
 */
Route::get('/claim-awaiting-treatment', 'ClaimAwaitingTreatments\ClaimAwaitingTreatmentController@index')->name('claim.awaiting.treatment.index');
Route::get('/claim-awaiting-treatment/{claim}', 'ClaimAwaitingTreatments\ClaimAwaitingTreatmentController@show')->name('claim.awaiting.treatment.show');
Route::put('/claim-awaiting-treatment/{claim}/rejected', 'ClaimAwaitingTreatments\ClaimAwaitingTreatmentController@rejectedClaim')->name('claim.awaiting.treatment.rejected');
Route::put('/claim-awaiting-treatment/{claim}/self-assignment', 'ClaimAwaitingTreatments\ClaimAwaitingTreatmentController@selfAssignment')->name('claim.awaiting.treatment.selfAssignment');
Route::put('/claim-awaiting-treatment/{claim}/assignment', 'ClaimAwaitingTreatments\ClaimAwaitingTreatmentController@assignmentClaimStaff')->name('claim.awaiting.treatment.assignmentClaimStaff');
Route::put('/claim-awaiting-treatment/{claim}/unfounded', 'ClaimAwaitingTreatments\ClaimAwaitingTreatmentController@unfoundedClaim')->name('claim.awaiting.treatment.unfoundedClaim');
