<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    /**
     * --- Тестирование метода incrementCreatedQuizzesCounter ---
     */

    /**
     * Тест успешного увеличения счетчика созданных викторин.
     */
    public function test_increment_created_quizzes_counter_successful(): void
    {
        // Создаем пользователя через фабрику
        $user = User::factory()->create([
            'login' => 'testuser',
            'created_quizzes_counter' => 5
        ]);

        // Отправляем запрос с логином пользователя
        $response = $this->postJson('/api/increment-created-quizzes-counter', [
            'login' => $user->login,
        ]);

        // Проверяем статус ответа и структуру JSON
        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'login' => $user->login,
                    'created_quizzes_counter' => 6,
                ],
                'message' => 'Счетчик созданных викторин успешно обновлен',
            ]);

        // Проверяем обновление в базе данных
        $this->assertDatabaseHas('users', [
            'login' => $user->login,
            'created_quizzes_counter' => 6
        ]);

        $user->delete();
    }

    /**
     * Тест ошибки при отсутствии логина пользователя.
     */
    public function test_increment_created_quizzes_counter_fails_without_login(): void
    {
        // Отправляем запрос без логина пользователя
        $response = $this->postJson('/api/increment-created-quizzes-counter', []);

        // Проверяем статус ответа и сообщение об ошибке
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'login' => 'Поле login обязательно для заполнения.'
            ]);
    }

    /**
     * Тест ошибки при несуществующем логине пользователя.
     */
    public function test_increment_created_quizzes_counter_fails_with_invalid_login(): void
    {
        // Отправляем запрос с несуществующим логином
        $response = $this->postJson('/api/increment-created-quizzes-counter', [
            'login' => 'nonexistent_user',
        ]);

        // Проверяем статус ответа и сообщение об ошибке
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Пользователь не найден',
            ]);
    }

    /**
     * Тест корректной обработки при нулевом значении счетчика.
     */
    public function test_increment_created_quizzes_counter_from_zero(): void
    {
        $user = User::factory()->create([
            'login' => 'newuser',
            'created_quizzes_counter' => 0
        ]);

        $response = $this->postJson('/api/increment-created-quizzes-counter', [
            'login' => $user->login,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'created_quizzes_counter' => 1,
                ]
            ]);

        $user->delete();
    }
}
