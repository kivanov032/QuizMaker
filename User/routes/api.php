<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MailSenderController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SystemController;
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

// Регистрация и авторизация
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);
});

Route::post('/validate-signup', [AuthController::class, 'validateSignup']);
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);

//Инкрементации
Route::post('/increment-created-quizzes-counter', [UserController::class, 'incrementCreatedQuizzesCounter']);

//Подтверждение кода
Route::post('/send-mail-for-code-confirmation', [MailSenderController::class, 'sendMailForCodeConfirmation']);
Route::post('/confirm-code', [MailSenderController::class, 'confirmCode']);
