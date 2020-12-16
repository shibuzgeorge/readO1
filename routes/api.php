<?php

use Illuminate\Support\Facades\Route;
use App\User;

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
Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/users', 'Admin\UsersController@index');

    Route::namespace('Module')->prefix('module')->name('module.')->group(function () {
        Route::resource('/v', 'ViewController');
    });

    Route::namespace('Textbook')->prefix('textbook')->name('textbook.')->group(function () {
        Route::resource('/upload', 'UploadController');
        Route::resource('/view', 'ViewController');
    });


    Route::namespace('Admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('/users', 'UsersController', ['except' => ['show', 'create', 'store']]);
    });

    Route::get('/upload', 'Textbook\UploadController@index');


});
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'Auth\LoginController@logout');

    Route::get('/user', 'Auth\UserController@current');
    Route::get('/user/role', 'Auth\UserController@checkRole');



    Route::patch('settings/profile', 'Settings\ProfileController@update');
    Route::patch('settings/password', 'Settings\PasswordController@update');
});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');

    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');

    Route::post('email/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify');
    Route::post('email/resend', 'Auth\VerificationController@resend');

    Route::post('oauth/{driver}', 'Auth\OAuthController@redirectToProvider');
    Route::get('oauth/{driver}/callback', 'Auth\OAuthController@handleProviderCallback')->name('oauth.callback');
});
