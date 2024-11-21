<?php

use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//USERS
Route::get('/users/reactivate-account', [UserController::class, 'reactivateAccount']);
Route::post('/users/login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->get('/users/getByToken', function (Request $request) {
    return $request->user()->load('followers', 'following');
});
Route::get('/users/search-users', [UserController::class, 'searchUsers']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::post('/users/changePrivacy', [UserController::class, 'changeAccountPrivacy'])->middleware('auth:sanctum');
Route::resource('/users', UserController::class)->only(['index', 'store', 'destroy']);

//FOLLOWS
Route::get('/follows', [FollowController::class, 'index']);
Route::post('/follow/{id}', [FollowController::class, 'follow'])->middleware('auth:sanctum');
Route::delete('/unfollow/{id}', [FollowController::class, 'unfollow'])->middleware('auth:sanctum');
Route::get('/followers/{id}', [FollowController::class, 'getFollowers'])->middleware('auth:sanctum');
Route::get('/followings/{id}', [FollowController::class, 'getFollowings'])->middleware('auth:sanctum');

//POSTS
Route::get('/posts', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store'])->middleware('auth:sanctum');
Route::get('/friendsPosts', [PostController::class, 'getUsersPosts'])->middleware('auth:sanctum');
Route::get('/userPosts/{userId}', [PostController::class, 'getCurrentUserPosts'])->middleware('auth:sanctum');

Route::delete('/posts/{id}', [PostController::class, 'destroy'])->middleware('auth:sanctum');

//LIKES
Route::get('/likes', [LikeController::class, 'index']);
Route::get('/postLikes/{postId}', [LikeController::class, 'getPostLikes']);
Route::post('/likes/{postId}', [LikeController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/likes/{postId}', [LikeController::class, 'destroy'])->middleware('auth:sanctum');
