<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'replace']); // Para actualizacion completa
    Route::patch('/users/{user}', [UserController::class, 'update']); // Para actualizacion parcial
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::post('/users/{id}/restore', [UserController::class, 'restore']);
});
