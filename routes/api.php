<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
// use App\Http\Controllers\OneDriveController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function (Request $request){
    return "Hello! this is api response";
});

// --- Posts & Users ---
Route::apiResource('/posts' , PostController::class);
Route::apiResource('/users' , UserController::class);
