<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ConfirmCodeConfirmationRequest;
use App\Http\Requests\SendCodeConfirmationRequest;
use App\Models\CodeConfirmation;
use Illuminate\Http\JsonResponse;
use App\Services\MailSenderService;
use Illuminate\Http\Request;

class MailSenderController
{
    protected MailSenderService $service;

    public function __construct(MailSenderService $service)
    {
        $this->service = $service;
    }

    /**
     * Отправляет письмо с кодом подтверждения на указанный email.
     *
     * Проверяет переданный email через валидированный запрос, генерирует код подтверждения,
     * сохраняет его в базе данных и отправляет письмо с кодом на указанный адрес.
     * В случае успешной отправки возвращает статус успеха. При ошибке возвращает сообщение.
     *
     * @OA\Post(
     *     path="/api/send-mail-for-code-confirmation",
     *     summary="Отправка письма с кодом подтверждения",
     *     description="Метод отправляет письмо с кодом подтверждения на указанный email.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные для отправки письма",
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="kivanov032@gmail.com",
     *                 description="Email пользователя, на который будет отправлен код подтверждения."
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Письмо успешно отправлено",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success",
     *                 description="Статус операции."
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Сообщение успешно доставлено пользователю.",
     *                 description="Сообщение об успешной отправке."
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Поле email обязательно для заполнения.",
     *                 description="Сообщение об ошибке валидации."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="Поле email обязательно для заполнения."
     *                     )
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error",
     *                 description="Статус операции."
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Ошибка отправки письма.",
     *                 description="Сообщение об ошибке."
     *             ),
     *         )
     *     )
     * )
     *
     * @param SendCodeConfirmationRequest $request Валидированный запрос с email пользователя.
     * @return JsonResponse Ответ с результатом отправки письма.
     */
    public function sendMailForCodeConfirmation(SendCodeConfirmationRequest $request): JsonResponse
    {
        return $this->service->sendCode($request);
    }


    /**
     * Подтверждает код подтверждения.
     *
     * Проверяет переданный код через валидированный запрос, ищет его в базе данных.
     * В случае успешного подтверждения возвращает статус успеха. При ошибке возвращает сообщение.
     *
     * @OA\Post(
     *     path="/api/confirm-code",
     *     summary="Подтверждение кода",
     *     description="Метод проверяет код подтверждения и возвращает статус успеха или ошибки.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Код подтверждения",
     *         @OA\JsonContent(
     *             required={"input_code"},
     *             @OA\Property(
     *                 property="input_code",
     *                 type="string",
     *                 example="123456",
     *                 description="Код подтверждения. Должен быть 6-значным числом."
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное подтверждение",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success",
     *                 description="Статус успешного подтверждения."
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Код подтверждения верен.",
     *                 description="Сообщение об успешном подтверждении."
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка подтверждения",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error",
     *                 description="Статус ошибки."
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Неверный код подтверждения.",
     *                 description="Сообщение об ошибке."
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Поле input code должно быть не меньше 6 символов.",
     *                 description="Сообщение об ошибке валидации."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="input_code",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="Поле input code должно быть не меньше 6 символов."
     *                     ),
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Произошла внутренняя ошибка сервера.",
     *                 description="Сообщение об ошибке."
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Сообщение об ошибке",
     *                 description="Детали ошибки."
     *             ),
     *         )
     *     )
     * )
     *
     * @param ConfirmCodeConfirmationRequest $request Валидированный запрос с кодом подтверждения.
     * @return JsonResponse Ответ со статусом подтверждения или сообщение об ошибке.
     */
    public function confirmCode(ConfirmCodeConfirmationRequest $request): JsonResponse
    {
        return $this->service->confirmCode($request);
    }

}
