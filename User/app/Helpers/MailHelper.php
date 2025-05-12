<?php

namespace App\Helpers;

use App\Models\CodeConfirmation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Random\RandomException;

class MailHelper
{
    /**
     * Генерация 6-разрядного числа для проверки отправки на почту.
     *
     * @return string
     * @throws RandomException
     */
    public static function generateCodeConfirmation(): string
    {
        $code = random_int(0, 999999);// Генерация случайного числа от 0 до 999999
        // Форматирование числа до 6 знаков с ведущими нулями
        return str_pad($code, 6, '0', STR_PAD_LEFT);
        //return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Создание или обновление записи в таблице code_confirmations.
     *
     * @param string $email Email пользователя
     * @param string $code Код подтверждения
     * @return CodeConfirmation
     */
    public static function createCodeConfirmation(string $email, string $code): CodeConfirmation
    {
        // Поиск записи по email или создание новой
        return CodeConfirmation::updateOrCreate(
            ['email' => $email], // Условие поиска
            [
                'id_code_confirmation' => Str::uuid(), // Генерация UUID
                'code_confirmation' => $code, // Данные для обновления или создания
                'updated_at' => now(), // Обновляем поле updated_at
            ]
        );
    }


    /**
     * Поиск записи по коду подтверждения и проверка времени.
     *
     * @param string $inputCode Код подтверждения.
     * @return array|null Результат проверки.
     */
    public static function findCodeConfirmation(string $email, string $inputCode): ?array
    {
        // Поиск записи по коду подтверждения
        $record = CodeConfirmation::where('email', $email)->first();

        if (!$record) {
            return null;
        }

        // Проверка времени
        $currentTime = Carbon::now();
        $updatedAt = Carbon::parse($record->updated_at);
        $createdAt = Carbon::parse($record->created_at);
        $timeDifference = $updatedAt->diffInMinutes($currentTime);

        Log::info($record->id_code_confirmation);
        Log::info($record->updated_at);
        Log::info($record->created_at);
        Log::info((string) $timeDifference);

        Log::info($record->code_confirmation);
        Log::info($record->code_confirmation);

        if ($timeDifference > 2.1) {
            return [
                'status' => 'error',
                'message' => 'Время ожидания закончилось. Пожалуйста, запросите новый код.',
            ];
        }


        if ($record->code_confirmation != $inputCode) {
            return [
                'status' => 'error',
                'message' => 'Неверный код подтверждения.',
            ];
        }

        return [
            'status' => 'success',
            'record' => $record,
        ];
    }

}
