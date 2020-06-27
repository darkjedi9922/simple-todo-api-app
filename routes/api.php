<?php

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

Route::get('/users', 'UserController@list');
Route::get('/users/{user:user_id}', 'UserController@item');
Route::post('/users', 'UserController@create');

Route::post('/profile/login', 'UserController@login');
Route::middleware('auth:api')->group(function () {
    Route::post('/profile/edit', 'UserController@editSelf');
    Route::delete('/profile', 'UserController@deleteSelf');
});

Route::fallback(function () {
    return response(['message' => 'Not Found'], 404);
});