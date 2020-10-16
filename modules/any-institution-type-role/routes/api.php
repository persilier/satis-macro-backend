<?php

use Illuminate\Support\Facades\Route;


Route::prefix('/any')->name('any.')->group(function () {
    /**
     * Roles
     */
    Route::resource('roles', 'Role\RoleController');
    Route::post('/roles/permissions/list', 'Permission\PermissionController@index')->name('roles.permissions.index');
});
