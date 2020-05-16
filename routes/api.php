<?php


use Illuminate\Support\Facades\Route;

// Public Route

// 認証済みユーザーのみのRoute group
Route::group(['middleware' => ['auth:api']], function(){

    //--
});

// guestのみのRoute
Route::group(['middleware' => ['guest:api']], function(){
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('verification/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify');
    Route::post('verification/resend', 'Auth\VerificationController@resend');
});
