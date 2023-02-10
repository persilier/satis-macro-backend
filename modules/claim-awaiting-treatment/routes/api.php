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
Route::get('/claim-awaiting-treatment/{claim}/edit', 'ClaimAwaitingTreatments\ClaimAwaitingTreatmentController@edit')->name('claim.awaiting.treatment.edit');
Route::put('/claim-awaiting-treatment/{claim}/rejected', 'ClaimAwaitingTreatments\ClaimAwaitingTreatmentController@rejectedClaim')->name('claim.awaiting.treatment.rejected');
Route::put('/claim-awaiting-treatment/{claim}/self-assignment', 'ClaimAwaitingTreatments\ClaimAwaitingTreatmentController@selfAssignmentClaim')->name('claim.awaiting.treatment.selfAssignment');
Route::put('/claim-awaiting-treatment/{claim}/assignment', 'ClaimAwaitingTreatments\ClaimAwaitingTreatmentController@assignmentClaimStaff')->name('claim.awaiting.treatment.assignmentClaimStaff');

/*
 * ClaimAwaitingTreatment to Ad'hoc
 */
Route::get('/claim-awaiting-adhoc-treatment', 'ClaimAwaitingTreatments\ClaimAwaitingTreatmentToAdhocController@index')->name('claim.awaiting.adhoc.treatment.index');
Route::put('/claim-assignment-adhoc-staff/{claim}/treatment', 'ClaimAssignmentToStaffs\ClaimAssignmentToStaffAdhocController@treatmentClaim')->name('claim.assignment.adhoc.staff.treatment');

Route::put('/claim-assignment-adhoc-staff/{claim}/closing', 'ClaimAssignmentToStaffs\ClaimAssignmentToStaffAdhocController@closedClaim')->name('claim.assignment.adhoc.staff.closing');




/*
 * ClaimAssignmentToSaff
 */
Route::get('/claim-assignment-staff', 'ClaimAssignmentToStaffs\ClaimAssignmentToStaffController@index')->name('claim.assignment.staff.index');
Route::get('/claim-assignment-staff/{claim}', 'ClaimAssignmentToStaffs\ClaimAssignmentToStaffController@show')->name('claim.assignment.staff.show');
Route::put('/claim-assignment-staff/{claim}/treatment', 'ClaimAssignmentToStaffs\ClaimAssignmentToStaffController@treatmentClaim')->name('claim.assignment.staff.treatment');
Route::put('/claim-assignment-staff/{claim}/unfounded', 'ClaimAssignmentToStaffs\ClaimAssignmentToStaffController@unfoundedClaim')->name('claim.assignment.staff.unfounded');

/*
 * Claim Reassignment
 */

Route::resource('claim-reassignment', 'ClaimReassignment\ClaimReassignmentController')->except(['store', 'destroy']);
/*
 * PilotTreatment
 */
Route::put('/claim-awaiting-assignment/{claim}/unfounded', 'PilotTreatment\UnfoundedClaimController@update')->name('claim.awaiting.assignment.unfounded');
