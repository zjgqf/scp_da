<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/user', 'ScpController@userList')->name('Scp.user');
Route::post('/department', 'ScpController@departmentList')->name('Scp.department');

Route::get('/pushSelfLine', 'DingtalkController@pushSelfLine')->name('Dingtalk.pushSelfLine');

Route::get('/customs','InterfacesController@index')->name('interface.index');