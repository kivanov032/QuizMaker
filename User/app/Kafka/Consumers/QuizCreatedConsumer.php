<?php

namespace App\Kafka\Consumers;

use App\Services\AuthService;
use Carbon\Exceptions\Exception;
use Junges\Kafka\Exceptions\ConsumerException;
use Junges\Kafka\Facades\Kafka;
use Illuminate\Support\Facades\Log;

class QuizCreatedConsumer
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @throws Exception
     * @throws ConsumerException
     */
    public function consume(): void
    {
        Kafka::consumer(['quiz_created'])
            ->withAutoCommit()
            ->withHandler(function ($message) {
                try {
                    $login = $message->getBody()['login'];
                    Log::info("Получено сообщение из Kafka: ", ['login' => $login]);
                    $this->authService->incrementQuizCounterForUser($login);
                } catch (\Exception $e) {
                    Log::error("Ошибка при обработке сообщения: " . $e->getMessage());
                }
            })
            ->build()
            ->consume();

    }
}




