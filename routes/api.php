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

    Route::get('/skips', [\App\Http\Controllers\SkipController::class, 'index']);
    Route::post('/skips', [\App\Http\Controllers\SkipController::class, 'store']);
    Route::post('/skips/{skip}/status', [\App\Http\Controllers\SkipController::class, 'updateStatus']);

    Route::post('/skips/{skip}/extensions', [\App\Http\Controllers\SkipExtensionController::class, 'store']);

    Route::get('skips/export', [\App\Http\Controllers\SkipController::class, 'exportSkipsToCsv']);

    Route::get('users', [\App\Http\Controllers\UserController::class, 'index']);

    Route::middleware([\App\Http\Middleware\RoleMiddleware::class . ':dean,admin'])->group(function () {
        Route::post('users/add-to-group', [\App\Http\Controllers\UserController::class, 'addToGroup']);
        Route::post('users/remove-from-group', [\App\Http\Controllers\UserController::class, 'removeFromGroup']);
    });

    Route::get('groups/all', [\App\Http\Controllers\GroupController::class, 'index']);
    Route::get('groups/get-group-users', [\App\Http\Controllers\GroupController::class, 'getGroupUsers']);
    Route::post('groups/create', [\App\Http\Controllers\GroupController::class, 'store']);

});
