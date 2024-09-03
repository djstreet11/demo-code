<?php

use Illuminate\Support\Facades\Route;

Route::get('/all', 'all')->name('all');
Route::get('/{id}/edit', 'edit')->name('edit');
Route::get('/add', 'add')->name('add');
Route::post('/create', 'create')->name('create');
Route::post('/{id}/update', 'update')->name('update');
Route::get('/{id}/delete', 'delete')->name('delete');
