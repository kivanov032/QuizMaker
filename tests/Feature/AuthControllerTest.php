<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    /**
     * Тесты для Входа
     */

    // Успешный логин
    public function test_login_successful_with_valid_credentials(): void
    {
        // Создаем пользователя через фабрику
        $user = \App\Models\User::factory()->create([
            'email' => 'password123@example.com',
            'password' => bcrypt('password/123'),
        ]);

        // Отправляем запрос
        $response = $this->postJson('/api/login', [
            'email' => 'password123@example.com',
            'password' => 'password/123',
        ]);

        // Проверяем успешный ответ
        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);

        // Удаляем созданного пользователя
        $user->delete();
    }

    // Ошибка, если email невалидный
    public function test_login_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password/123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    // Ошибка, если пароль не передан
    public function test_login_fails_with_missing_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            // Пароль отсутствует
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    // Ошибка, если пользователь с таким email не существует
    public function test_login_fails_if_user_does_not_exist(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password/123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    // Ошибка, если переданы пустые данные
    public function test_login_fails_with_empty_data(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }


    /**
     * Тесты Регистрации
     */


    // Успешную регистрация
    public function test_signup_successful_with_valid_data(): void
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password/123',
            'password_confirmation' => 'password/123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'token']);

        // Получаем данные созданного пользователя
        $user = json_decode($response->getContent(), true)['user'];

        // Удаляем пользователя после теста
        User::find($user['id_user'])->delete();
    }

    // Ошибка, если логин не передан
    public function test_signup_fails_without_login(): void
    {
        $response = $this->postJson('/api/signup', [
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['login']);
    }

    // Ошибка, если логин уже существует
    public function test_signup_fails_if_login_is_taken(): void
    {
        // Создаем пользователя с таким же логином
        $user = \App\Models\User::factory()->create(['login' => 'newuser1']);

        $response = $this->postJson('/api/signup', [
            'login' => 'newuser1',
            'email' => 'newuser@example.com',
            'password' => 'password/123',
            'password_confirmation' => 'password/123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['login']);

        $user->delete();
    }

    // Ошибка, если email невалидный
    public function test_signup_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'newuser',
            'email' => 'not-an-email',
            'password' => 'password/123',
            'password_confirmation' => 'password/123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    // Ошибка, если пароль не передан
    public function test_signup_fails_without_password(): void
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'newuser',
            'email' => 'newuser@example.com',
            // Пароль отсутствует
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    // Ошибка, если пароли не совпадают
    public function test_signup_fails_if_passwords_do_not_match(): void
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password/123',
            'password_confirmation' => 'password/1234',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    // Ошибка, если пароль слишком короткий
    public function test_signup_fails_if_password_is_too_short(): void
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    // Ошибка, если пароль не содержит символов
    public function test_signup_fails_if_password_does_not_contain_symbols(): void
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }


    /**
     * Тесты Выхода
     */



    // Успешного выход (удаления токена)
    public function test_logout_successfully(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('main')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(204);

        $user->delete();
    }


    // Выхода без авторизации (без токена)
    public function test_logout_fails_without_token(): void
    {
        $response = $this->postJson('/api/logout');
        $response->assertStatus(401);
    }
}
