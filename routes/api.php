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

Route::middleware('auth:api')->namespace('Api')->group(function (){
    
    Route::resource('/products', 'ProductController', ['except'=>'edit']);
    // Route::post('/products', 'ProductController@store');
    // Route::get('/products','ProductController@index');
    // Route::get('/products/{id}', 'ProductController@show');
    // Route::put('/products/{id}', 'ProductController@update');
    // Route::delete('/products/{id}', 'ProductController@delete');
});