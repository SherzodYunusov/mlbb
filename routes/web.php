<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/webapp/sell', function () {
    return view('webapp.sell');
});

Route::get('/webapp', function () {
    return view('webapp.marketplace');
});

Route::get('/profile/{telegramId}', [ProfileController::class, 'show'])
    ->where('telegramId', '[0-9]+')
    ->name('profile.show');

