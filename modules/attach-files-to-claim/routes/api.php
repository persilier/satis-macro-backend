<?php


use Illuminate\Support\Facades\Route;

Route::post('/attach-files-to-claim/{claim}', 'AttachFiles\AttachFilesController@index')->name('attach-files-to-claim');
