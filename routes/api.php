<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\ResendVerificationController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\FriendController;
use App\Http\Controllers\MessageController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/users', [UserController::class, 'index']);

    Route::post('/friends/add/{id}', [FriendController::class, 'addFriend']);
    Route::post('/friends/accept/{id}', [FriendController::class, 'acceptFriend']);

    Route::get('/friends', [FriendController::class, 'listFriends']);
    Route::get('/friends/requests', [FriendController::class, 'friendRequests']);

    Route::post('/message/send/{receiverId}]', [MessageController::class, 'send']);
    Route::get('/messages/{userId}', [MessageController::class, 'conversation']);
});

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/email/resend', [ResendVerificationController::class, 'send'])
->middleware('throttle:6,1');

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['throttle:6,1'])
    ->name('verification.verify');


