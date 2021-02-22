<?php

use App\Http\Middleware\CheckToken;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', 'Auth\AuthController@register');
Route::post('/login', 'Auth\AuthController@login');
Route::get('/logout', 'Auth\AuthController@logout');

Route::post('/fbregister', 'Auth\AuthController@fbregister');
Route::post('/fblogin', 'Auth\AuthController@fblogin');

Route::get('/checktoken', 'Auth\AuthController@token_check');

Route::get('/company/load', 'Api\ApiController@company_load');

Route::post('/survey/select', 'Api\ApiController@survey_select');
Route::get('/survey/load/{id?}', 'Api\ApiController@survey_load')->middleware(CheckToken::class);

Route::put('/comment/add', 'Api\ApiController@comment_add');


Route::post('/pass/forgot', 'Password\PasswordResetController@forgot');
Route::get('/pass/find/{token}', 'Password\PasswordResetController@find');
Route::post('/pass/reset', 'Password\PasswordResetController@reset');
