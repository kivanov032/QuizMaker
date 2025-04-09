<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyUserServerAboutUserQuiz implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1; // Количество попыток

    protected $quizData;

    // Константа для пути к API
    private const API_PATH = '/api/increment-created-quizzes-counter';

    public function __construct(array $quizData)
    {
        $this->quizData = $quizData;
    }

    public function handle(): void
    {
        try {
            // Получаем базовый URL из переменной окружения
            $baseUrl = env('USER_API_BASE_URL');
            $url = $baseUrl . self::API_PATH; // Формируем полный URL

            $response = Http::post($url, [
                'id_user' => $this->quizData['id_user']
            ]);

            if ($response->successful()) {
                Log::info('Сервер успешно оповещён о создании викторины пользователем: ' . $this->quizData['id_user']);
            } else {
                Log::error('Ошибка при оповещении сервера User о создании викторины пользователем: ' . $response->body());
                throw new \Exception('Ошибка внешнего сервера: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Ошибка в задаче NotifyUserServerAboutUserQuiz: ' . $e->getMessage());
            throw $e; // Повторная попытка
        }
    }
}
