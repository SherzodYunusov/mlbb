<?php

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

