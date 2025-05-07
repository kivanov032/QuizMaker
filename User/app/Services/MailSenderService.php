<?php

namespace App\Services;

use App\Http\Requests\ConfirmCodeConfirmationRequest;
use App\Http\Requests\SendCodeConfirmationRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\CodeConfirmationMail;
use App\Helpers\MailHelper;
use Illuminate\Http\JsonResponse;

class MailSenderService
{
    // Отправка кода подтверждения на почту
    public function sendCode(SendCodeConfirmationRequest $request): JsonResponse
    {
        $email = $request->input('email');

        $code = MailHelper::generateCodeConfirmation(); // Генерация случайного 6-значного кода
        $record = MailHelper::createCodeConfirmation($email, $code); // Занесение данного кода в бд
        $data = ['message' => $record->code_confirmation]; // Формирование запрос для его отправки по почте

        try {
            Mail::to($email)->send(new CodeConfirmationMail($data));  // Отправка кода подтверждения на почту
            return response()->json([
                'status' => 'success',
                'message' => 'Сообщение успешно доставлено пользователю.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка отправки письма.',
            ], 500);
        }
    }

    // Подтверждение кода подтверждения
    public function confirmCode(ConfirmCodeConfirmationRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $input_code = $request->input('input_code');

        $result = MailHelper::findCodeConfirmation($email, $input_code); // Поиск записи по коду подтверждения

        // Случай на неверный код подтверждения
        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Код подтверждения не отправлялся.',
            ], 400);
        }

        // Случай на истёкший код подтверждения
        if ($result['status'] === 'error') {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], 400);
        }

        // Позитивный сценарий
        return response()->json([
            'status' => 'success',
            'message' => 'Код подтверждения верен.',
        ], 200);
    }
}
