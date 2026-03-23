<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\OneDriveController;
use App\Http\Controllers\GoogleDriveController;


// Start OAuth (redirect to Microsoft)
Route::get('/auth/onedrive', function () {
    $clientId    = config('services.onedrive.client_id');
    $redirectUri = urlencode(config('services.onedrive.redirect'));
    $scopes      = urlencode('files.readwrite offline_access user.read');

    $url = "https://login.microsoftonline.com/common/oauth2/v2.0/authorize?"
        . "client_id={$clientId}"
        . "&response_type=code"
        . "&redirect_uri={$redirectUri}"
        . "&response_mode=query"
        . "&scope={$scopes}";

    return redirect($url);
});

// ✅ Keep ONLY controller callback (remove any duplicate)
Route::get('/auth/onedrive/callback', [OneDriveController::class, 'callback']);


Route::get('/auth/google', [GoogleDriveController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleDriveController::class, 'handleGoogleCallback']);

Route::get('/google/drive/root', [GoogleDriveController::class, 'listMyDrive']);
Route::get('/google/drive/shared', [GoogleDriveController::class, 'listSharedWithMe']);
Route::get('/google/drive/folder/{id}', [GoogleDriveController::class, 'listFolderContents']);

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');


