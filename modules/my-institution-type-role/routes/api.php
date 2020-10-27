<?php

use Illuminate\Support\Facades\Route;


Route::prefix('/my')->name('my.')->group(function () {
    /**
     * Roles
     */
    Route::resource('roles', 'Role\RoleController');
});
