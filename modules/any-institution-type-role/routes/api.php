<?php

use Illuminate\Support\Facades\Route;


Route::prefix('/any')->name('any.')->group(function () {
    /**
     * Roles
     */
    Route::resource('roles', 'Role\RoleController');
    Route::post('/roles/permissions/list', 'Permission\PermissionController@index')->name('roles.permissions.index');
    Route::post('/roles/add-profil/import', 'ImportExport\AddRolesToProfilsController@store')->name('roles.add-roles-profils.store');
});
