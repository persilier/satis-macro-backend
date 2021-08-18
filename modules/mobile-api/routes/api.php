<?php


use Illuminate\Support\Facades\Route;

Route::post('/mobile/statistiques', 'Statistique\StatistiqueController@statistiques')->name('mobile.statistiques');
