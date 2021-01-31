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
Route::group(['middleware' => 'auth:api'], function () {

    Route::get('/users', 'Admin\UsersController@index');

    Route::namespace('Admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('/users', 'UsersController', ['except' => ['show', 'create', 'store']]);
    });

    //Module Controller
    Route::get('module/getUsersForModule/{module_id}', 'ModuleController@getUsersForModule');
    Route::get('module/getAllStudents', 'ModuleController@getAllStudents');
    Route::post('module/assignStudents/{module_id}', 'ModuleController@assignStudents');
    Route::post('module/assignModuleTutors/{module_id}', 'ModuleController@assignModuleTutors');
    Route::get('module/getAllModuleTutors', 'ModuleController@getAllModuleTutors');
    Route::get('module/getModuleById/{module_id}', 'ModuleController@getModuleById');
    Route::resource('/module', 'ModuleController');

    Route::resource('/textbook', 'TextbookController');
    Route::get('textbook/pdf/{textbook_id}', 'TextbookController@pdf');

    Route::resource('/yearGroup', 'YearGroupController');

    Route::resource('/text', 'TextController');
    Route::get('/text/pdf/{textbook_id}', 'TextController@pdf');

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
