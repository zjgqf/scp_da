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
Route::get('/', 'PagesController@index')->name('pages.index');

Route::get('/scp/account', 'AccountsController@index')->name('accounts.index');
Route::get('/scp/accounts','AccountsController@show')->name('accounts.show');
Route::post('/scp/accounts/export','AccountsController@export')->name('accounts.export');

Route::get('/scp/single', 'SinglesController@index')->name('singles.index');
Route::get('/scp/singles', 'SinglesController@show')->name('singles.show');
Route::post('/scp/singes/export', 'SinglesController@export')->name('singles.export');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


