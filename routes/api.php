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

Route::get('/tasks', 'TaskController@list');
Route::middleware('auth:api')->group(function () {
    Route::post('/tasks', 'TaskController@create');
    Route::post('/tasks/edit/{task}', 'TaskController@edit');
    Route::post('/tasks/{task}/status', 'TaskController@setStatus');
    Route::post('/tasks/{task}/user', 'TaskController@setUser');
    Route::delete('/tasks/{task}', 'TaskController@delete');
});

Route::fallback(function () {
    return response(['message' => 'Not Found'], 404);
});