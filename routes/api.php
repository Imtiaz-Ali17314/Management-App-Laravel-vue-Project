<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OneDriveController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function (Request $request){
    return "Hello! this is api response";
});

// --- Posts & Users ---
Route::apiResource('/posts' , PostController::class);
Route::apiResource('/users' , UserController::class);

// --- OneDrive Routes ---
Route::prefix('onedrive')->group(function () {
    Route::get('callback', [OneDriveController::class, 'callback']);          // OAuth callback
    Route::get('files', [OneDriveController::class, 'listFiles']);            // List files/folders ?path=/
    Route::post('upload', [OneDriveController::class, 'uploadFile']);         // Upload file
    Route::post('folder', [OneDriveController::class, 'createFolder']);       // Create folder
    Route::get('download/{itemId}', [OneDriveController::class, 'downloadFile']); // Download file
    Route::delete('delete/{itemId}', [OneDriveController::class, 'deleteItem']);  // Delete file/folder
});
