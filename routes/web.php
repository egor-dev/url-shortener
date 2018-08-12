<?php

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

// переход по короткой ссылке
Route::get('/{code}', 'LinksController@forward');

// статистика переходов по короткой ссылке
Route::get('/{code}/hits', 'LinksController@hits');

// главная
Route::get('/', 'LinksController@index');

// создание короткой ссылки
Route::post('/', 'LinksController@store');

