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
*/

/*
 * Models
 */
Route::resource('metadata.data', 'Metadata\MetadataController', ['except' => ['create','edit']]);
Route::resource('formulaire', 'Formulaire\FormulaireController', ['except' => ['create']]);
Route::name('formulaire.create')->get('formulaire/{formulaire}/create', 'Formulaire\FormulaireController@create');
Route::resource('header', 'Header\HeaderController', ['except' => ['create']]);
Route::name('header.create')->get('header/{header}/create', 'Header\HeaderController@create');
Route::post('/installation/next', 'Installation\InstallationController@next')->name('installation.next');
