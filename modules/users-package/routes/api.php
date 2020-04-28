<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|

/*
 * Roles
 */
Route::resource('roles', 'Role\RoleController', ['except' => ['create', 'edit']]);
Route::resource('roles.permissions', 'Role\RolePermissionController', ['only' => ['store', 'destroy']]);
Route::name('give.all.permissions')->post('give-all-permissions', 'Role\RolePermissionController@give_all');

/*
 * Permissions, Identites, Staffs, Clients
 */
Route::apiResource('permissions', 'Permission\PermissionController');
Route::apiResource('identites', 'Identite\IdentiteController');
Route::resource('identites.staff', 'Identite\IdentiteStaffController', ['only' => ['store']]);
Route::resource('identites.client', 'Identite\IdentiteClientController', ['only' => ['store']]);
/**
 * Users
 */

Route::resource('users', 'User\UserController', ['except' => ['edit', 'update']]);
Route::resource('users.roles', 'User\UserRoleController', ['only' => ['index', 'store']]);
Route::resource('users.permissions', 'User\UserPermissionController', ['only' => ['index']]);
Route::name('verify')->get('users/verify/{token}', 'User\UserController@verify');
Route::name('resend')->get('users/{user}/resend', 'User\UserController@resend');

/**
 * Authentication
 */
Route::name('login')->get('login', 'Auth\AuthController@login');
Route::name('logout')->get('logout', 'Auth\AuthController@logout');