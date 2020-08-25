<?php

use Illuminate\Support\Facades\Route;


Route::prefix('/my')->name('my.')->group(function () {

    /**
     * Users
     */

    Route::resource('users', 'User\UserController', ['except' => ['edit', 'update']]);
    /**
     * change password user
     */
    Route::put('/users/{user}/change-password', 'User\UserController@changePassword')->name('user.changePassword');

    /**
     * enabled | desabled user account
     */
    Route::put('/users/{user}/enabled-desabled', 'User\UserController@enabledDesabled')->name('user.enabledDesabled');
    /**
     * Change user role
     */
    Route::get('/users/{user}/change-role', 'User\UserController@getAllRoles')->name('user.getAllRoles');
    Route::put('/users/{user}/change-role', 'User\UserController@changeUserRole')->name('user.changeUserRole');
});