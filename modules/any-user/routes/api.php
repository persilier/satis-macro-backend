<?php

use Illuminate\Support\Facades\Route;


Route::prefix('/any')->name('any.')->group(function () {

    /**
     * Users
     */

    Route::resource('users', 'User\UserController', ['except' => ['edit', 'update']]);
    Route::get('/users/{institution}/create', 'IdentiteRole\IdentiteRoleController@index')->name('user.identite-role.index');

    /**
     * enabled | desabled user account
     */
    Route::put('/users/{user}/enabled-desabled', 'User\UserController@enabledDesabled')->name('user.enabledDesabled');

    /**
     * change password user
     */
    //Route::put('/users/{user}/change-password', 'User\UserController@changePassword')->name('user.changePassword');
    /**
     * Change user role
     */
    Route::get('/users/{user}/change-role-password', 'User\UserController@getUserUpdate')->name('user.getUserUpdate');
    Route::put('/users/{user}/change-role-password', 'User\UserController@userUpdate')->name('user.userUpdate');

});
