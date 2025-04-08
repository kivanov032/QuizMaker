<?php

use App\Http\Controllers\Api\CreatorQuizController;
use Illuminate\Support\Facades\Route;

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


