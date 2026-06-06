<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');


