<?php

use Illuminate\Support\Facades\Route;


Route::prefix('/my')->name('my.')->group(function () {
    /**
     * Roles
     */
    Route::resource('roles', 'Role\RoleController');
    Route::post('/roles/add-profil/import', 'ImportExport\AddRolesToProfilsController@store')->name('roles.add-roles-profils.store');
});
