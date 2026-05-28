<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login',    [AuthenticatedSessionController::class, 'store']);
Route::post('/logout',   [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:sanctum');

Route::get('/posts',              [PostController::class, 'index']);
Route::get('/posts/{id}',         [PostController::class, 'show']);
Route::get('/cities',             [CityController::class, 'index']);
Route::get('/cities/{id}/posts',  [CityController::class, 'posts']);
Route::get('/categories',         [CategoryController::class, 'index']);
Route::get('/users/{id}/profile', [FollowController::class, 'profile']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $r) => $r->user());

    Route::post('/posts',              [PostController::class, 'store']);
    Route::put('/posts/{post}',        [PostController::class, 'update']);
    Route::delete('/posts/{post}',     [PostController::class, 'destroy']);
    Route::post('/posts/{id}/like',    [PostController::class, 'like']);
    Route::post('/posts/{id}/comment', [PostController::class, 'comment']);

    Route::get('/messages',  [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);

    Route::post('/users/{id}/follow',   [FollowController::class, 'toggle']);
    Route::get('/users/{id}/followers', [FollowController::class, 'followers']);
    Route::get('/users/{id}/following', [FollowController::class, 'following']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/stats',         [AdminController::class, 'stats']);
        Route::get('/users',         [AdminController::class, 'users']);
        Route::get('/posts',         [AdminController::class, 'posts']);
        Route::delete('/posts/{id}', [AdminController::class, 'deletePost']);
    });
});
