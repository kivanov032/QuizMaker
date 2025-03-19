<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CreatorQuizController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/searchQuizErrors', [CreatorQuizController::class, 'searchQuizErrors']);
Route::post('/fixQuizErrors', [CreatorQuizController::class, 'fixQuizErrors']);
Route::post('/createQuizWithQuestions', [CreatorQuizController::class, 'createQuizWithQuestions']);
Route::get('/checkConnectionWithBD', [CreatorQuizController::class, 'checkConnectionWithBD']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

});

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);



/*
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('/users', UserController::class);
});

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
*/
