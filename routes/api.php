<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\ResendVerificationController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\FriendController;
use App\Http\Controllers\User\FriendRequestController;
use App\Http\Controllers\MessageController;

Route::middleware('auth:sanctum')->group(function () {
    //Authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //Active users
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);

    //Friend management
    Route::post('/friend-requests', [FriendRequestController::class, 'store']);
    Route::patch('/friend-requests/{id}', [FriendRequestController::class, 'update']);
    Route::get('/friend-requests', [FriendRequestController::class, 'index']);
    Route::get('/friend-requests/incoming', [FriendRequestController::class, 'incoming']);
    Route::get('/friend-requests/outgoing', [FriendRequestController::class, 'outgoing']);
    
    Route::get('/friends', [FriendController::class, 'index']);
    
    //Message managemant
    Route::post('/message', [MessageController::class, 'store']);
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/{userId}', [MessageController::class, 'show']);
});
Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['throttle:6,1'])
    ->name('verification.verify');


