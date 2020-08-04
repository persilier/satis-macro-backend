<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('mail', function () {
    $claim = \Satis2020\ServicePackage\Models\Claim::find("a9a79643-cffc-443f-a7d9-df53e1fb8f81");
    $identity = \Satis2020\ServicePackage\Models\Identite::find("ed2f15a9-2b1b-4549-b48f-65fcf50d8cd9");

    return $identity->notify(new \Satis2020\ServicePackage\Notifications\RegisterAClaim($claim));
});
