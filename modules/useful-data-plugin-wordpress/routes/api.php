<?php
use Illuminate\Support\Facades\Route;

Route::prefix('/plugin')->name('plugin.')->group(function () {
    Route::get('/{accountNumber}/client', 'Clients\ClientsController@show')->name('client.show');
});