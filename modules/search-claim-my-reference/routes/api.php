<?php


use Illuminate\Support\Facades\Route;

Route::prefix('/my')->name('my.')->group(function () {

    Route::get('search-claim/{reference}', 'Search\SearchController@index')->name('search.claim');
});
