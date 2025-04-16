<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PassingQuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PassingQuizController extends Controller
{
    private PassingQuizService $passingQuizService;

    /**
     * Конструктор контроллера для создания экземпляра Сервиса
     *
     * @param PassingQuizService $passingQuizService
     */
    public function __construct(PassingQuizService $passingQuizService)
    {
        $this->passingQuizService = $passingQuizService;
    }

    /**
     * Поиск викторин по названию
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchQuiz(Request $request): JsonResponse
    {
        return $this->passingQuizService->searchQuiz($request);
    }

    /**
     * Загрузка вопросов викторины по её ID
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function downloadQuizQuestions(Request $request): JsonResponse
    {
        return $this->passingQuizService->downloadQuizQuestions($request);
    }

    /**
     * Анализ прохождения викторины
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkQuizAnswers(Request $request): JsonResponse
    {
        return $this->passingQuizService->checkQuizAnswers($request);
    }

}
