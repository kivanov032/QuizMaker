<?php

namespace App\Helpers;

use Random\RandomException;

class MailHelper
{
    /**
     * Генерация 6-разрядного числа для проверки отправки на почту.
     *
     * @return string
     * @throws RandomException
     */
    public static function generateVerificationCode(): string
    {
        // Генерация случайного числа от 0 до 999999
        $code = random_int(0, 999999);

        // Форматирование числа до 6 знаков с ведущими нулями
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

}
