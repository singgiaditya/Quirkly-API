<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\api\CompaniesController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Auth
Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});

//User
Route::middleware('auth.api')->group(function(){
    // Route::controller(UserController::class)->group(function(){
    //     Route::get('users', 'index');
    //     Route::get('users/{id}', 'show');
    // });

    Route::apiResource('users' , UserController::class);
    Route::apiResource('companies' , CompaniesController::class);
});
