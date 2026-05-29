<?php

use Illuminate\Support\Facades\Route;

// DOPFood SPA – All web requests serve the same blade view.
// Client-side hash router handles navigation.
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
