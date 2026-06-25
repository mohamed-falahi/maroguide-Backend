<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login',    [AuthenticatedSessionController::class, 'store']);
Route::post('/logout',   [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:sanctum');

// Public routes (no authentication required)
Route::get('/posts',              [PostController::class, 'index']);
Route::get('/posts/{id}',         [PostController::class, 'show']);
Route::get('/posts/{id}/comments', [PostController::class, 'getComments']);
Route::get('/cities',             [CityController::class, 'index']);
Route::get('/cities/{id}/posts',  [CityController::class, 'posts']);
Route::get('/categories',         [CategoryController::class, 'index']);
Route::get('/users/{id}/profile', [FollowController::class, 'profile']);
Route::get('/users/{id}',          [UserController::class, 'show']);
Route::get('/users/{id}/posts',    [UserController::class, 'getUserPosts']);
Route::get('/users/search',        [UserController::class, 'search']);

Route::middleware('auth:sanctum')->group(function () {
    // User profile routes
    Route::get('/user',              [UserController::class, 'getCurrentUser']);
    Route::put('/user/profile',      [UserController::class, 'updateProfile']);
    Route::post('/user/avatar',      [UserController::class, 'updateAvatar']);
    Route::post('/user/cover',       [UserController::class, 'updateCover']);
    Route::put('/user/password',     [UserController::class, 'changePassword']);
    Route::delete('/user/account',   [UserController::class, 'deleteAccount']);

    // Post routes
    Route::post('/posts',              [PostController::class, 'store']);
    Route::put('/posts/{post}',        [PostController::class, 'update']);
    Route::delete('/posts/{post}',     [PostController::class, 'destroy']);
    Route::post('/posts/{id}/like',    [PostController::class, 'like']);
    Route::post('/posts/{id}/comment', [PostController::class, 'comment']);

    // Message routes
    Route::get('/messages',  [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);

    // Follow routes
    Route::post('/users/{id}/follow',   [FollowController::class, 'toggle']);
    Route::get('/users/{id}/followers', [FollowController::class, 'followers']);
    Route::get('/users/{id}/following', [FollowController::class, 'following']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/stats',         [AdminController::class, 'stats']);
        Route::get('/users',         [AdminController::class, 'users']);
        Route::get('/posts',         [AdminController::class, 'posts']);
        Route::delete('/posts/{id}', [AdminController::class, 'deletePost']);

        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        // City management routes
        Route::get('/cities',         [AdminController::class, 'cities']);
        Route::post('/cities',        [AdminController::class, 'storeCity']);
        Route::put('/cities/{id}',    [AdminController::class, 'updateCity']);
        Route::delete('/cities/{id}', [AdminController::class, 'deleteCity']);

        // Category management routes
        Route::get('/categories',         [AdminController::class, 'categories']);
        Route::post('/categories',        [AdminController::class, 'storeCategory']);
        Route::put('/categories/{id}',    [AdminController::class, 'updateCategory']);
        Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory']);
    });
});
