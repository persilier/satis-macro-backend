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
//Route::resource('metadata.data', 'Metadata\MetadataController', ['except' => ['create','edit']]);
//Route::resource('formulaire', 'Formulaire\FormulaireController', ['except' => ['create']]);
//Route::name('formulaire.create')->get('formulaire/{formulaire}/create', 'Formulaire\FormulaireController@create');
//Route::resource('header', 'Header\HeaderController', ['except' => ['create']]);
//Route::name('header.create')->get('header/{header}/create', 'Header\HeaderController@create');
//Route::post('/installation/next', 'Installation\InstallationController@next')->name('installation.next');

Route::get('plan', 'Plan\PlanController@show')->name('plan.show');
Route::put('plan', 'Plan\PlanController@update')->name('plan.update');
Route::get("reporting-metadata",'Reporting\ReportingMetadataController@index')->name('reporting.meta');
Route::get("reporting-metadata/{name}",'Reporting\ReportingMetadataController@edit')->name('edit.reporting.meta');
Route::put("reporting-metadata",'Reporting\ReportingMetadataController@update')->name('update.reporting.meta');


Route::get("proxy-metadata",'Proxy\ProxyConfigMetadataController@index')->name('proxy.meta');
Route::put("update-proxy-metadata",'Proxy\ProxyConfigMetadataController@update')->name('update.proxy.meta');
Route::delete("delete-proxy-metadata",'Proxy\ProxyConfigMetadataController@delete')->name('delete.proxy.meta');
