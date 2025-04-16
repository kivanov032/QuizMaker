<?php
namespace Tests\Feature;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    /**
     * --- Тестирование метода checkActivity ---
     */

    /**
     * Тест успешного подключения к базе данных.
     */
    public function test_check_activity_successful_database_connection(): void
    {
        // Вызов метода
        $response = $this->getJson('/api/check-activity');

        // Проверка статуса ответа и структуры JSON
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Сервер активен, соединение с БД успешно установлено.',
                'server_status' => 'Активен',
                'database_status' => 'Подключение к БД успешно',
            ]);
    }

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
        $response->assertStatus(200);

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


    /**
     * --- Тестирование метода getUser ---
     */

    /**
     * Тест успешного получения данных пользователя.
     */
    public function test_get_user_successful_with_valid_token()
    {
        // Создаём пользователя
        $user = User::factory()->create([
            'email' => 'newuser@example.com',
            'login' => 'newuser',
        ]);

        // Устанавливаем время истечения токена так же, как в signup
        $expiresAt = now()->addDays(2);

        // Создаём токен
        $token = $user->createToken('main', ['*'], $expiresAt)->plainTextToken;

        // Делаем запрос с заголовком авторизации
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        // Проверяем ответ
        $response->assertStatus(200)
            ->assertJsonFragment([
                'user' => [
                    'id_user' => $user->id_user,
                    'email' => 'newuser@example.com',
                    'login' => 'newuser',
                    'created_quizzes_counter' => 0,
                    'taken_quizzes_counter' => 0,
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString(),
                ],
                'message' => 'Токен действителен',
            ]);

        // Чистим пользователя
        $user->delete();
    }




    /**
     * Тест ошибки при отсутствии токена.
     */
    public function test_get_user_fails_without_token(): void
    {
        // Отправляем запрос без токена
        $response = $this->getJson('/api/user');

        // Проверяем статус ответа и сообщение об ошибке
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Не авторизован.',
            ]);
    }

    /**
     * Тест ошибки при недействительном токене.
     */
    public function test_get_user_fails_with_invalid_token(): void
    {
        // Отправляем запрос с недействительным токеном
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->getJson('/api/user');

        // Проверяем статус ответа и сообщение об ошибке
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Не авторизован.',
            ]);
    }

    /**
     * Тест внутренней ошибки сервера.
     */
    public function test_get_user_fails_with_server_error(): void
    {
        // Создаем пользователя через фабрику
        $user = User::factory()->create();

        // Аутентифицируем пользователя
        $token = $user->createToken('auth_token')->plainTextToken;

        // Имитируем внутреннюю ошибку сервера
        $this->mock(AuthController::class, function ($mock) {
            $mock->shouldReceive('getUser')
                ->andThrow(new \Exception('Произошла внутренняя ошибка сервера.'));
        });

        // Отправляем запрос с токеном
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        // Проверяем статус ответа и сообщение об ошибке
        $response->assertStatus(500);

        // Удаляем созданного пользователя
        $user->delete();
    }




}


