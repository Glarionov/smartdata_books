<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\AuthController;

Route::group([
    'middleware' => 'api',
    'prefix' => '/v1/books/'

], function($router) {
    Route::post('/update', [BookController::class, 'updateBookData']);
    Route::post('/create', [BookController::class, 'create']);
    Route::get('/{id}', [BookController::class, 'loadById']);
    Route::get('/list/{id}', [BookController::class, 'loadAll']);
    Route::delete('/{id}', [BookController::class, 'deleteById']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => '/v1/authors/'

], function($router) {
    Route::post('/update', [AuthorController::class, 'updateAuthorData']);
    Route::post('/load-by-substring', [AuthorController::class, 'loadBySubstring']);
    Route::get('/load-for-admin', [AuthorController::class, 'loadForAdmin']);
    Route::delete('delete/{id}', [AuthorController::class, 'deleteById']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => '/v1/auth/'

], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('user-profile', [AuthController::class, 'userProfile']);
});
