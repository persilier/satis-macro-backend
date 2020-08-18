<?php

use Illuminate\Support\Facades\Route;


Route::prefix('/my')->name('my.')->group(function () {

    /**
     * Users
     */

    Route::resource('users', 'User\UserController', ['except' => ['edit', 'update']]);

});