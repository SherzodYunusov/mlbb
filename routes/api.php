<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AccountRequestCommentController;
use App\Http\Controllers\Api\AccountRequestController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BuyController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Webhook — throttle yo'q (Telegram serveri ko'p so'rov yuboradi)
Route::post('/webhook/telegram', [WebhookController::class, 'handle']);

// Auth — minutiga 60 ta so'rov
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/auth/verify', [AuthController::class, 'verify']);
});

// Asosiy API — minutiga 200 ta so'rov
Route::middleware('throttle:200,1')->group(function () {
    Route::get('/accounts/mine',                    [AccountController::class, 'mine']);
    Route::get('/accounts',                         [AccountController::class, 'index']);
    Route::put('/accounts/{account}',               [AccountController::class, 'update']);
    Route::delete('/accounts/{account}',            [AccountController::class, 'destroy']);
    Route::post('/accounts/{account}/mark-sold',    [AccountController::class, 'markSold']);
    Route::post('/accounts/{account}/view',         [AccountController::class, 'viewAccount']);
    Route::post('/buy/{account}',                   [BuyController::class, 'buy']);
    Route::get('/accounts/{account}/comments',      [CommentController::class, 'index']);
    Route::put('/comments/{comment}',               [CommentController::class, 'update']);
    Route::delete('/comments/{comment}',            [CommentController::class, 'destroy']);
});

// Izoh qoldirish — minutiga 60 ta
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/accounts/{account}/comments',     [CommentController::class, 'store']);
});

// Yuklash — minutiga 30 ta
Route::middleware('throttle:30,1')->group(function () {
    Route::post('/accounts', [AccountController::class, 'store']);
});

// Akkaunt so'rovlari (buyurtmalar)
Route::middleware('throttle:200,1')->group(function () {
    Route::get('/account-requests',                              [AccountRequestController::class, 'index']);
    Route::post('/account-requests/{id}/close',                  [AccountRequestController::class, 'close']);
    Route::get('/account-requests/{id}/comments',               [AccountRequestCommentController::class, 'index']);
    Route::delete('/account-request-comments/{commentId}',       [AccountRequestCommentController::class, 'destroy']);
});

Route::middleware('throttle:30,1')->group(function () {
    Route::post('/account-requests',                            [AccountRequestController::class, 'store']);
    Route::post('/account-requests/{id}/comments',              [AccountRequestCommentController::class, 'store']);
});
