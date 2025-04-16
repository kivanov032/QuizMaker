<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Увеличивает счетчик созданных викторин для пользователя.
     *
     * Принимает UUID пользователя, находит его в системе и увеличивает счетчик созданных викторин.
     * Возвращает обновленные данные пользователя и сообщение об успешном обновлении.
     *
     * @OA\Post(
     *     path="/api/increment-created-quizzes-counter",
     *     summary="Увеличение счетчика созданных викторин",
     *     description="Метод принимает UUID пользователя, находит его в системе и увеличивает счетчик созданных викторин.",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные для обновления счетчика",
     *         @OA\JsonContent(
     *             required={"id_user"},
     *             @OA\Property(
     *                 property="id_user",
     *                 type="string",
     *                 format="uuid",
     *                 example="50f65337-8506-466a-9502-b4003ff9fab2",
     *                 description="UUID пользователя, для которого нужно увеличить счетчик."
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное обновление счетчика",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id_user", type="string", format="uuid", example="d8dd125c-3670-4d87-a946-14b9067d7ede"),
     *                 @OA\Property(property="login", type="string", example="test"),
     *                 @OA\Property(property="email", type="string", example="test@example.com"),
     *                 @OA\Property(property="created_quizzes_counter", type="integer", example=1),
     *                 @OA\Property(property="taken_quizzes_counter", type="integer", example=0),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-13T23:42:12.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-13T23:42:12.000000Z"),
     *             ),
     *             @OA\Property(property="message", type="string", example="Счетчик созданных викторин успешно обновлен"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Пользователь не найден",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Пользователь не найден"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Поле id user должно быть действительным UUID."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="id_user", type="array",
     *                     @OA\Items(type="string", example="Поле id user должно быть действительным UUID.")
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message": "Internal Server Error",
     *                 "error": "SQLSTATE[HY000] [2002] Connection refused"
     *             }
     *         )
     *     )
     * )
     *
     * @param Request $request Запрос, содержащий UUID пользователя.
     * @return JsonResponse Ответ с обновленными данными пользователя или сообщение об ошибке.
     */
    public function incrementCreatedQuizzesCounter(Request $request)
    {
        return $this->userService->incrementCreatedQuizzesCounter($request);
    }
}

