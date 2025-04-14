<?php

namespace App\Console\Commands;

use App\Kafka\Consumers\QuizCreatedConsumer;
use App\Services\AuthService;
use Carbon\Exceptions\Exception;
use Illuminate\Console\Command;

class ConsumeQuizCreated extends Command
{
    protected $signature = 'kafka:consume-quiz';
    protected $description = 'Запускает Kafka Consumer для обработки событий quiz_created';

    public function handle(): void
    {
        $this->info('Запуск Kafka Consumer для топика quiz_created...');

        try {
            new QuizCreatedConsumer(app(AuthService::class))->consume();
        } catch (\Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
            logger()->error("Kafka Consumer упал: " . $e->getMessage());
        } catch (Exception $e) {
        }
    }
}
