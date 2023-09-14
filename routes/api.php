<?php

use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\ColumnController;
use App\Http\Controllers\Api\TagController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('cards', CardController::class);
    Route::apiResource('tags', TagController::class)->only(['store', 'show', 'index']);
    Route::apiResource('column', ColumnController::class);
    Route::get('/me', [\App\Http\Controllers\Api\AuthController::class, 'me'])->name('me');
    Route::apiResource('boards', \App\Http\Controllers\Api\BoardController::class);
});


Route::post('/registration', [\App\Http\Controllers\Api\AuthController::class, 'registration'])->name('registration');
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login'])->name('login');
