<?php

use Illuminate\Support\Facades\Route;
use Satis2020\UserPackage\Http\Controllers\Auth\AuthController;
use Satis2020\UserPackage\Http\Controllers\Auth\LogoutController;

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
Route::resource('identites.client-from-my-institution', 'Identite\IdentiteClientController', ['only' => ['store']]);
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
/**
 * Profile
 */
Route::name('edit.profil')->get('edit-profil', 'Profile\ProfileController@edit');
Route::name('update.profil')->put('update-profil', 'Profile\ProfileController@update');
Route::name('change.password')->put('change-password', 'Profile\UpdatePasswordController@update');

/**
 * Password Reset
 */

Route::name('forgot.password')->post('/forgot-password', 'Auth\PasswordResetController@create');
Route::name('reset.password')->get('/forgot-password/{token}', 'Auth\PasswordResetController@find');
Route::name('reset.password.post')->post('/reset-password', 'Auth\PasswordResetController@reset');
Route::name('reset.password.expired')->put('/reset-password-expired', 'Auth\PasswordExpiredResetController@update');

Route::post("/login",[AuthController::class,"store"])->name("issue.token");
Route::post("/logout",[LogoutController::class,"store"])->name("logout.user");