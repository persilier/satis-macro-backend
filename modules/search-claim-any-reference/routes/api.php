<?php


use Illuminate\Support\Facades\Route;

Route::prefix('/any')->name('any.')->group(function () {

    Route::get('search-claim/{reference}', 'Search\SearchController@index')->name('search.claim');
});
