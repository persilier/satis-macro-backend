<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/any')->name('any.')->group(function () {
    Route::get('/email-claim-configuration', 'Configurations\ConfigurationsController@edit')->name('email.claim.configuration.edit');
    Route::post('/email-claim-configuration/{emailClaimConfiguration?}', 'Configurations\ConfigurationsController@store')->name('email.claim.configuration.store');
    Route::post('institutions/{email}/register-email-claim','IncomingMails\IncomingMailsController@store')->name('register-email-claim');
});
