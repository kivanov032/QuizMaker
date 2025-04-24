<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\UserController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SystemControllerTest extends TestCase
{

//    /**
//     * Тест успешного выполнения запроса.
//     */
//    public function test_increment_created_quizzes_counter_succeeds_with_success_response(): void
//    {
//        // Создаем пользователя через фабрику
//        $user = User::factory()->create();
//
//        // Замокать успешное выполнение запроса
//        $this->mock(UserController::class, function ($mock) use ($user) {
//            $mock->shouldReceive('where')
//                ->once()
//                ->with('login', $user->login)
//                ->andReturnSelf();
//            $mock->shouldReceive('first')
//                ->once()
//                ->andReturn($user);
//        });
//
//        // Отправляем запрос с UUID пользователя
//        $response = $this->postJson('/api/increment-created-quizzes-counter', [
//            'login' => $user->login,
//        ]);
//
//        // Проверяем, что статус ответа 200 (успешно)
//        $response->assertStatus(200);
//
//        // Проверяем, что в ответе есть ожидаемое сообщение
//        $response->assertJson([
//            'message' => 'Счетчик созданных викторин обновлен',
//        ]);
//
//        // Удаляем созданного пользователя
//        $user->delete();
//    }

    /**
     * Тест внутренней ошибки сервера.
     */
    public function test_increment_created_quizzes_counter_fails_with_server_error(): void
    {
        // Создаем пользователя через фабрику
        $user = User::factory()->create();

        // Имитируем внутреннюю ошибку сервера
        $this->mock(UserController::class, function ($mock) {
            $mock->shouldReceive('where->first')
                ->andThrow(new \Exception('Произошла внутренняя ошибка сервера.'));
        });

        // Отправляем запрос с UUID пользователя
        $response = $this->postJson('/api/increment-created-quizzes-counter', [
            'id_user' => $user->id_user,
        ]);

        // Проверяем статус ответа и сообщение об ошибке
        $response->assertStatus(500);

        // Удаляем созданного пользователя
        $user->delete();
    }
}
