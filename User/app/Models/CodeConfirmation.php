<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CodeConfirmation extends Model
{
    use HasFactory;

    /**
     * Название таблицы, связанной с моделью.
     *
     * @var string
     */
    protected $table = 'code_confirmations';

    /**
     * Первичный ключ таблицы.
     *
     * @var string
     */
    protected $primaryKey = 'id_code_confirmation';

    /**
     * Тип первичного ключа.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Указывает, что первичный ключ не является автоинкрементным.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array
     */
    protected $fillable = [
        'id_code_confirmation',
        'email',
        'code_confirmation',
    ];

    /**
     * Атрибуты, которые должны быть скрыты при сериализации.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Атрибуты, которые должны быть приведены к определённым типам.
     *
     * @var array
     */
    protected $casts = [
        'id_code_confirmation' => 'string',
        'email' => 'string',
        'code_confirmation' => 'integer',
    ];

    /**
     * Создание или обновление записи в таблице code_confirmations.
     *
     * @param string $email Email пользователя
     * @param string $code Код подтверждения
     * @return CodeConfirmation
     */
    public static function createRecord(string $email, string $code): CodeConfirmation
    {
        // Поиск записи по email или создание новой
        return self::updateOrCreate(
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
    public static function findValidCode(string $inputCode): ?array
    {
        // Поиск записи по коду подтверждения
        $record = self::where('code_confirmation', $inputCode)->first();

        if (!$record) {
            return null; // Запись не найдена
        }

        // Проверка времени
        $currentTime = Carbon::now();
        $updatedAt = Carbon::parse($record->updated_at);
        $timeDifference = $updatedAt->diffInMinutes($currentTime);

        if ($timeDifference > 2.5) {
            return [
                'status' => 'error',
                'message' => 'Время ожидания закончилось. Пожалуйста, запросите новый код.',
            ];
        }

        // Возвращаем запись, если время не истекло
        return [
            'status' => 'success',
            'record' => $record,
        ];
    }
}
