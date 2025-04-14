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
                    $id_user = $message->getBody()['id_user'];
                    Log::info("Получено сообщение из Kafka: ", ['id_user' => $id_user]);
                    $this->authService->incrementQuizCounterForUser($id_user);
                } catch (\Exception $e) {
                    Log::error("Ошибка при обработке сообщения: " . $e->getMessage());
                }
            })
            ->build()
            ->consume();

    }
}




