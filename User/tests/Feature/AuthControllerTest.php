<?php
namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    /**
     *  --- Тестирование метода checkActivity ---
     */


    /**
     *  --- Тестирование метода login ---
     */

    /**
     * Тест успешного входа с валидными данными.
     */
    public function test_login_successful_with_valid_credentials(): void
    {
        // Создаем пользователя через фабрику
        $user = User::factory()->create([
            'login' => 'password123',
            'password' => bcrypt('password/123'),
        ]);

        // Отправляем запрос на вход
        $response = $this->postJson('/api/login', [
            'login' => 'password123',
            'password' => 'password/123',
        ]);

        // Проверяем статус ответа и структуру JSON
        $response->assertStatus(200)
            ->assertJsonStructure(['token']);

        // Удаляем созданного пользователя
        $user->delete();
    }

    /**
     * Тест ошибки при входе с невалидным логином.
     */
    public function test_login_fails_with_invalid_login_format(): void
    {
        $response = $this->postJson('/api/login', [
            'login' => 'not-an-email',
            'password' => 'password/123',
        ]);

        // Проверяем статус ответа и наличие ошибки валидации
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login']);
    }

    /**
     * Тест ошибки при входе без пароля.
     */
    public function test_login_fails_with_missing_password(): void
    {
        $response = $this->postJson('/api/login', [
            'login' => 'test@example.com',
            // Пароль отсутствует
        ]);

        // Проверяем статус ответа и наличие ошибки валидации
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Тест ошибки при входе, если пользователь не существует.
     */
    public function test_login_fails_if_user_does_not_exist(): void
    {
        $response = $this->postJson('/api/login', [
            'login' => '123456',
            'password' => 'password/123',
        ]);

        // Проверяем статус ответа и наличие ошибки валидации
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login']);
    }

    /**
     * Тест ошибки при входе с пустыми данными.
     */
    public function test_login_fails_with_empty_data(): void
    {
        $response = $this->postJson('/api/login', []);

        // Проверяем статус ответа и наличие ошибок валидации
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login', 'password']);
    }

    /**
     *  --- Тестирование метода signup ---
     */

    /**
     * Тест успешной регистрации с валидными данными.
     */
    public function test_signup_successful_with_valid_data(): void
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password/123',
            'password_confirmation' => 'password/123',
        ]);

        // Проверяем статус ответа и структуру JSON
        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'token']);

        // Получаем данные созданного пользователя
        $user = json_decode($response->getContent(), true)['user'];

        // Удаляем пользователя после теста
        User::find($user['id_user'])->delete();
    }

    /**
     * Тест ошибки при регистрации без логина.
     */
    public function test_signup_fails_without_login(): void
    {
        $response = $this->postJson('/api/signup', [
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Проверяем статус ответа и наличие ошибки валидации
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login']);
    }

    /**
     * Тест ошибки при регистрации, если логин уже занят.
     */
    public function test_signup_fails_if_login_is_taken(): void
    {
        // Создаем пользователя с таким же логином
        $user = User::factory()->create(['login' => 'newuser1']);

        $response = $this->postJson('/api/signup', [
            'login' => 'newuser1',
            'email' => 'newuser@example.com',
            'password' => 'password/123',
            'password_confirmation' => 'password/123',
        ]);

        // Проверяем статус ответа и наличие ошибки валидации
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login']);

        // Удаляем созданного пользователя
        $user->delete();
    }

    /**
     * Тест ошибки при регистрации с невалидным email.
     */
    public function test_signup_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'newuser',
            'email' => 'not-an-email',
            'password' => 'password/123',
            'password_confirmation' => 'password/123',
        ]);

        // Проверяем статус ответа и наличие ошибки валидации
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Тест ошибки при регистрации без пароля.
     */
    public function test_signup_fails_without_password(): void
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'newuser',
            'email' => 'newuser@example.com',
            // Пароль отсутствует
        ]);

        // Проверяем статус ответа и наличие ошибки валидации
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Тест ошибки при регистрации, если пароли не совпадают.
     */
    public function test_signup_fails_if_passwords_do_not_match(): void
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password/123',
            'password_confirmation' => 'password/1234',
        ]);

        // Проверяем статус ответа и наличие ошибки валидации
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Тест ошибки при регистрации, если пароль слишком короткий.
     */
    public function test_signup_fails_if_password_is_too_short(): void
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        // Проверяем статус ответа и наличие ошибки валидации
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Тест ошибки при регистрации, если пароль не содержит символов.
     */
    public function test_signup_fails_if_password_does_not_contain_symbols(): void
    {
        $response = $this->postJson('/api/signup', [
            'login' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Проверяем статус ответа и наличие ошибки валидации
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     *  --- Тестирование метода logout ---
     */

    /**
     * Тест успешного выхода (удаления токена).
     */
    public function test_logout_successfully(): void
    {
        // Создаем пользователя и токен
        $user = User::factory()->create();
        $token = $user->createToken('main')->plainTextToken;

        // Отправляем запрос на выход с токеном
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        // Проверяем статус ответа
        $response->assertStatus(204);

        // Удаляем созданного пользователя
        $user->delete();
    }

    /**
     * Тест ошибки при выходе без авторизации (без токена).
     */
    public function test_logout_fails_without_token(): void
    {
        $response = $this->postJson('/api/logout');

        // Проверяем статус ответа
        $response->assertStatus(401);
    }


}


