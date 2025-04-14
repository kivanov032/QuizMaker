<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MailSenderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Random\RandomException;

class MailSenderController extends Controller
{
    protected MailSenderService $mailSenderService;

    /**
     * Конструктор контроллера.
     *
     * @param MailSenderService $mailSenderService Сервис для отправки писем и подтверждения кодов.
     */
    public function __construct(MailSenderService $mailSenderService)
    {
        $this->mailSenderService = $mailSenderService;
    }

    /**
     * Отправка письма с кодом подтверждения.
     *
     * @param Request $request Запрос, содержащий данные для отправки письма (например, email).
     * @return JsonResponse Ответ с результатом операции.
     * @throws RandomException Исключение, которое может быть выброшено при генерации кода.
     */
    public function sendMailForCodeConfirmation(Request $request): JsonResponse
    {
        return $this->mailSenderService->sendMailForCodeConfirmation($request);
    }

    /**
     * Подтверждение кода, отправленного на email.
     *
     * @param Request $request Запрос, содержащий код для подтверждения.
     * @return JsonResponse Ответ с результатом проверки кода.
     */
    public function confirmCode(Request $request): JsonResponse
    {
        return $this->mailSenderService->confirmCode($request);
    }
}
