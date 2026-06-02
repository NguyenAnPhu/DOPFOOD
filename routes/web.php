<?php

use Illuminate\Support\Facades\Route;

// Phục vụ ảnh từ storage (khắc phục lỗi symlink trên Windows với php artisan serve)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (file_exists($fullPath)) {
        return response()->file($fullPath);
    }
    abort(404);
})->where('path', '.*');

// DOPFood SPA – All web requests serve the same blade view.
// Client-side hash router handles navigation.
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
