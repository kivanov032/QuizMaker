<?php

use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\CreatorQuizController;
use App\Http\Controllers\Api\PassingQuizController;
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

// Проверка соединения с бд
Route::get('/check-activity', [SystemController::class, 'checkActivity']);

// Создание викторины
Route::post('/search-quiz-errors', [CreatorQuizController::class, 'searchQuizErrors']);
Route::post('/fix-quiz-errors', [CreatorQuizController::class, 'fixQuizErrors']);
Route::post('/create-quiz', [CreatorQuizController::class, 'createQuiz']);

// Прохождение викторины
Route::post('/search-quiz', [PassingQuizController::class, 'searchQuiz']);
Route::post('/get-quiz-questions', [PassingQuizController::class, 'getQuizQuestions']);
Route::post('/check-quiz-answers', [PassingQuizController::class, 'checkQuizAnswers']);
