<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});


Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/roles-add', [\App\Http\Controllers\UserController::class, 'addRole']);

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update']);
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'get']);
    Route::post('/upload-csv', [\App\Http\Controllers\UserController::class, 'uploadCsv']);
});
