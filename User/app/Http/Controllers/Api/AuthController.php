<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    protected AuthService $authService;

    /**
     * Конструктор контроллера для создания экземпляра Сервиса
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Проверка активности сервера и подключения к базе данных.
     *
     * @return JsonResponse
     */
    public function checkActivity(): JsonResponse
    {
        return $this->authService->checkActivity();
    }

    /**
     * Регистрация нового пользователя.
     *
     * @param SignupRequest $request
     * @return Response
     */
    public function signup(SignupRequest $request): Response
    {
        return $this->authService->signup($request);
    }

    /**
     * Авторизация пользователя.
     *
     * @param LoginRequest $request
     * @return Response
     */
    public function login(LoginRequest $request): Response
    {
        return $this->authService->login($request);
    }

    /**
     * Выход пользователя из системы.
     *
     * @param Request $request
     * @return Response
     */
    public function logout(Request $request): Response
    {
        return $this->authService->logout($request);
    }

    /**
     * Получение информации о текущем аутентифицированном пользователе.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUser(Request $request): JsonResponse
    {
        return $this->authService->getUser($request);
    }

    /**
     * Увеличение счетчика созданных викторин для пользователя.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function incrementCreatedQuizzesCounter(Request $request): JsonResponse
    {
        return $this->authService->incrementCreatedQuizzesCounter($request);
    }
}
