<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
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
        $user = User::factory()->create(['created_quizzes_counter' => 5]);

        // Отправляем запрос с UUID пользователя
        $response = $this->postJson('/api/increment-created-quizzes-counter', [
            'id_user' => $user->id_user,
        ]);

        // Проверяем статус ответа и структуру JSON
        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id_user' => $user->id_user,
                    'created_quizzes_counter' => 6,
                ],
                'message' => 'Счетчик созданных викторин успешно обновлен',
            ]);

        // Удаляем созданного пользователя
        $user->delete();
    }

    /**
     * Тест ошибки при отсутствии UUID пользователя.
     */
    public function test_increment_created_quizzes_counter_fails_without_user_id(): void
    {
        // Отправляем запрос без UUID пользователя
        $response = $this->postJson('/api/increment-created-quizzes-counter', []);

        // Проверяем статус ответа и сообщение об ошибке
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Поле id user обязательно для заполнения.',
                'errors' => [
                    'id_user' => ['Поле id user обязательно для заполнения.'],
                ],
            ]);
    }

    /**
     * Тест ошибки при несуществующем UUID пользователя.
     */
    public function test_increment_created_quizzes_counter_fails_with_invalid_user_id(): void
    {
        // Генерируем случайный UUID
        $randomUuid = Str::uuid();

        // Отправляем запрос с несуществующим UUID
        $response = $this->postJson('/api/increment-created-quizzes-counter', [
            'id_user' => $randomUuid,
        ]);

        // Проверяем статус ответа и сообщение об ошибке
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Пользователь не найден',
            ]);
    }
}
