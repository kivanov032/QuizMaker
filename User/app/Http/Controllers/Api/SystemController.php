<?php

namespace App\Http\Controllers\Api;

use App\Services\SystemService;
use Illuminate\Http\JsonResponse;

class SystemController
{
    protected SystemService $systemService;

    public function __construct(SystemService $systemService)
    {
        $this->systemService = $systemService;
    }


    /**
     * Проверяет активность сервера и подключение к базе данных.
     *
     *
     * @OA\Get(
     *      path="/api/check-activity",
     *      summary="Проверка связи с базой данных",
     *      description="Метод проверяет активность сервера и соединение с базой данных.",
     *      tags={"UserDatabase"},
     *      @OA\Response(
     *          response=200,
     *          description="Успешное подключение к БД",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="message", type="string", example="Сервер активен, соединение с БД успешно установлено."),
     *              @OA\Property(property="server_status", type="string", example="Активен"),
     *              @OA\Property(property="database_status", type="string", example="Подключение к БД успешно"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Ошибка подключения к БД",
     *          @OA\JsonContent(
     *              oneOf={
     *                  @OA\Schema(
     *                      @OA\Property(property="status", type="string", example="error"),
     *                      @OA\Property(property="code", type="string", example="DB_CONNECTION_ERROR"),
     *                      @OA\Property(property="message", type="string", example="Ошибка подключения к БД."),
     *                      @OA\Property(property="server_status", type="string", example="Активен"),
     *                      @OA\Property(property="database_status", type="string", example="Ошибка подключения к БД"),
     *                      @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="status", type="string", example="error"),
     *                      @OA\Property(property="code", type="string", example="UNKNOWN_ERROR"),
     *                      @OA\Property(property="message", type="string", example="Неизвестная ошибка при подключении к БД."),
     *                      @OA\Property(property="server_status", type="string", example="Активен"),
     *                      @OA\Property(property="database_status", type="string", example="Неизвестная ошибка"),
     *                      @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *                  )
     *              }
     *          )
     *      )
     *  )
     *
     *
     * @return JsonResponse Ответ с состоянием сервера и БД.
     */
    public function checkActivity(): JsonResponse
    {
        $result = $this->systemService->checkActivity();
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }
}

