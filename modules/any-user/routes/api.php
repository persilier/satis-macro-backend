<?php

use Illuminate\Support\Facades\Route;


Route::prefix('/any')->name('any.')->group(function () {

    /**
     * Users
     */

    Route::resource('users', 'User\UserController', ['except' => ['edit', 'update']]);
    Route::get('/users/{institution}/create', 'IdentiteRole\IdentiteRoleController@index')->name('any.user.identite-role.index');

});