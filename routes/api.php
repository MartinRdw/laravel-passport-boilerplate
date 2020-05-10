<?php

use Illuminate\Http\Request;
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
|--------------------------------------------------------------------------
| Auth routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/user', 'Api\Auth\AuthController@user')->name('auth.user');
    Route::post('/logout', 'Api\Auth\AuthController@logout')->name('auth.logout');
});

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::post('/login', 'Api\Auth\AuthController@login')->name('auth.login');
Route::post('/register', 'Api\Auth\AuthController@register')->name('auth.register');