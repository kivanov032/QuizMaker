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

Route::post('/search-quiz-errors', [CreatorQuizController::class, 'searchQuizErrors']);
Route::post('/fix-quiz-errors', [CreatorQuizController::class, 'fixQuizErrors']);
Route::post('/create-quiz', [CreatorQuizController::class, 'createQuiz']);
Route::get('/check-activity', [CreatorQuizController::class, 'checkActivity']);


