<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\CodeConfirmationMail;
use App\Helpers\MailHelper;
use App\Models\CodeConfirmation;
use Illuminate\Http\JsonResponse;

class MailSenderService
{
    public function send(string $email): JsonResponse
    {
        Log::info("Полученные данные в метод send:", ['email' => $email]);

        $code = MailHelper::generateVerificationCode();
        $record = CodeConfirmation::createRecord($email, $code);

        $data = ['message' => $record->code_confirmation];

        try {
            Mail::to($email)->send(new CodeConfirmationMail($data));
            Log::info("Письмо отправлено на адрес: $email");

            return response()->json([
                'status' => 'success',
                'message' => 'Сообщение успешно доставлено пользователю.',
            ], 200);
        } catch (\Exception $e) {
            Log::error("Ошибка отправки письма: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка отправки письма.',
            ], 500);
        }
    }

    public function confirm(string $input_code): JsonResponse
    {
        Log::info("Полученные данные в метод confirm:", ['input_code' => $input_code]);
        $result = CodeConfirmation::findValidCode($input_code);

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Неверный код подтверждения.',
            ], 400);
        }

        if ($result['status'] === 'error') {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Код подтверждения верен.',
        ], 200);
    }
}
