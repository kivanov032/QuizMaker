<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    /**
     * Проверяет активность сервера и подключение к базе данных.
     *
     * @return JsonResponse Ответ с состоянием сервера и БД.
     */
    //Проверка связи с бд
    public function checkActivity(): \Illuminate\Http\JsonResponse
    {
        Log::info("Я в checkConnectionWithDB");
        $serverStatus = 'Активен';
        try {
            DB::connection()->getPdo();
            return response()->json([
                'status' => 'success',
                'message' => 'Сервер активен, соединение с БД успешно установлено.',
                'server_status' => $serverStatus,
                'database_status' => 'Подключение к БД успешно',
            ], 200);
        } catch (\PDOException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'DB_CONNECTION_ERROR',
                'message' => 'Ошибка подключения к БД.',
                'server_status' => $serverStatus,
                'database_status' => 'Ошибка подключения к БД',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'UNKNOWN_ERROR',
                'message' => 'Неизвестная ошибка при подключении к БД.',
                'server_status' => $serverStatus,
                'database_status' => 'Неизвестная ошибка',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Регистрирует нового пользователя.
     *
     * Принимает данные из валидированного запроса, создаёт пользователя,
     * генерирует UUID, хэширует пароль, создаёт токен авторизации
     * и возвращает пользователя вместе с токеном.
     *
     * @param SignupRequest $request Валидированный запрос с данными для регистрации.
     * @return Response Ответ с данными пользователя и токеном.
     */
    public function signup(SignupRequest $request): Response
    {
        $data = $request->validated();
        //Log::info('Signup request received', ['data' => $request->all()]);
        Log::info("Я в методе signup");
        $uuid = Str::uuid();
        /** @var User $user */
        $user = User::create([
            'id_user' => $uuid,
            'login' => $data['login'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $token = $user->createToken('main', ['*'], now()->addDays(2))->plainTextToken;

        return response(compact('user', 'token'));

    }

    /**
     * Авторизует пользователя.
     *
     * Проверяет переданные данные через валидированный запрос, выполняет попытку входа.
     * В случае успешной аутентификации создаёт токен авторизации
     * и возвращает пользователя с токеном. При ошибке возвращает сообщение.
     *
     * @param LoginRequest $request Валидированный запрос с данными для входа.
     * @return Response Ответ с данными пользователя и токеном или сообщение об ошибке.
     */
    public function login(LoginRequest $request): Response
    {
        Log::info("Я в методе login");
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response([
                'message' => 'Логин или пароль не верны.'
            ], 422);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('main', ['*'], now()->addDays(2))->plainTextToken;
        return response(compact('user', 'token'));
    }


    /**
     * Выполняет выход пользователя.
     *
     * Удаляет текущий access-токен пользователя и возвращает успешный ответ.
     *
     * @param Request $request Запрос, содержащий аутентифицированного пользователя.
     * @return Response Пустой ответ с кодом 204 (успешный выход).
     */
    public function logout(Request $request): Response
    {
        Log::info("Я в методе logout");
        /** @var User $user */
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return response('', 204);
    }


    /**
     * Получает информацию о текущем аутентифицированном пользователе.
     *
     * Возвращает данные пользователя и сообщение о действительности токена.
     *
     * @param Request $request Запрос, содержащий аутентифицированного пользователя.
     * @return JsonResponse Ответ в формате JSON с данными пользователя и сообщением.
     */
    public function getUser(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
            'message' => 'Токен действителен'
        ]);
    }

    public function incrementCreatedQuizzesCounter(Request $request): JsonResponse
    {
        // Валидация входящих данных
        $request->validate([
            'id_user' => 'required|uuid', // Убедитесь, что id_user передан и является UUID
        ]);

        // Получаем id_user из запроса
        $id_user = $request->input('id_user');

        // Находим пользователя по id_user
        $user = User::where('id_user', $id_user)->first();

        // Проверяем, существует ли пользователь
        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        // Увеличиваем счетчик созданных викторин
        $user->increment('created_quizzes_counter');

        // Возвращаем обновленные данные пользователя
        return response()->json([
            'user' => $user,
            'message' => 'Счетчик созданных викторин успешно обновлен',
        ]);
    }


}
