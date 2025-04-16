<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateQuizRequest;
use App\Services\CreatorQuizService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreatorQuizController extends Controller
{
    private CreatorQuizService $creatorQuizService;

    /**
     * Конструктор контроллера для создания экземпляра Сервиса
     *
     * @param CreatorQuizService $creatorQuizService
     */
    public function __construct(CreatorQuizService $creatorQuizService)
    {
        $this->creatorQuizService = $creatorQuizService;
    }

    /**
     * Проверка активности сервера и подключения к базе данных.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkActivity(Request $request): JsonResponse
    {
        return $this->creatorQuizService->checkActivity();
    }

    /**
     * Поиск ошибок в викторине.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchQuizErrors(Request $request): JsonResponse
    {
        return $this->creatorQuizService->searchQuizErrors($request);
    }

    /**
     * Исправление ошибок в викторине.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fixQuizErrors(Request $request): JsonResponse
    {
        return $this->creatorQuizService->fixQuizErrors($request);
    }

    /**
     * Создание новой викторины.
     *
     * @param CreateQuizRequest $request Валидированный запрос с данными для создания викторины.
     * @return JsonResponse
     * @throws Exception
     */
    public function createQuiz(CreateQuizRequest $request): JsonResponse
    {
        return $this->creatorQuizService->createQuiz($request);
    }
}
