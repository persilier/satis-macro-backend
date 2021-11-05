<?php
use Illuminate\Support\Facades\Route;

Route::prefix('/account')->name('account.')->group(function () {
    Route::get('/{accountNumber}/client', 'Clients\ClientsController@show')->name('client.show');
});