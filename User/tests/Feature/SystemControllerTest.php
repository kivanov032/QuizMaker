<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SystemControllerTest extends TestCase
{
    /**
     * --- Тестирование метода checkActivity ---
     */

    /**
     * Тест успешного подключения к базе данных.
     */
    //    public function test_check_activity_successful_database_connection(): void
    //    {
    //        // Вызов метода
    //        $response = $this->getJson('/api/check-activity');
    //
    //        // Проверка статуса ответа и структуры JSON
    //        $response->assertStatus(200)
    //            ->assertJson([
    //                'status' => 'success',
    //                'message' => 'Сервер активен, соединение с БД успешно установлено.',
    //                'server_status' => 'Активен',
    //                'database_status' => 'Подключение к БД успешно',
    //            ]);
    //    }

    /**
     * Тест ошибки подключения к базе данных.
     */
    public function test_check_activity_fails_with_database_connection_error(): void
    {
        // Имитируем ошибку подключения к БД
        DB::shouldReceive('connection->getPdo')
            ->andThrow(new \PDOException('Ошибка подключения к БД'));

        // Вызов метода
        $response = $this->getJson('/api/check-activity');

        // Проверка статуса ответа и структуры JSON
        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'code' => 'DB_CONNECTION_ERROR',
                'message' => 'Ошибка подключения к БД.',
                'server_status' => 'Активен',
                'database_status' => 'Ошибка подключения к БД',
            ]);
    }

    /**
     * Тест неизвестной ошибки при подключении к базе данных.
     */
    public function test_check_activity_fails_with_unknown_error(): void
    {
        // Имитируем неизвестную ошибку
        DB::shouldReceive('connection->getPdo')
            ->andThrow(new \Exception('Неизвестная ошибка'));

        // Вызов метода
        $response = $this->getJson('/api/check-activity');

        // Проверка статуса ответа и структуры JSON
        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'code' => 'UNKNOWN_ERROR',
                'message' => 'Неизвестная ошибка при подключении к БД.',
                'server_status' => 'Активен',
                'database_status' => 'Неизвестная ошибка',
            ]);
    }
}
