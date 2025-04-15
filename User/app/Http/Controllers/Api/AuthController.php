<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    /**
     * Проверяет активность сервера и подключение к базе данных.
     *
     *
     * @OA\Get(
     *      path="/api/check-activity",
     *      summary="Проверка связи с базой данных",
     *      description="Метод проверяет активность сервера и соединение с базой данных.",
     *      tags={"UserDatabase"},
     *      @OA\Response(
     *          response=200,
     *          description="Успешное подключение к БД",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="message", type="string", example="Сервер активен, соединение с БД успешно установлено."),
     *              @OA\Property(property="server_status", type="string", example="Активен"),
     *              @OA\Property(property="database_status", type="string", example="Подключение к БД успешно"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Ошибка подключения к БД",
     *          @OA\JsonContent(
     *              oneOf={
     *                  @OA\Schema(
     *                      @OA\Property(property="status", type="string", example="error"),
     *                      @OA\Property(property="code", type="string", example="DB_CONNECTION_ERROR"),
     *                      @OA\Property(property="message", type="string", example="Ошибка подключения к БД."),
     *                      @OA\Property(property="server_status", type="string", example="Активен"),
     *                      @OA\Property(property="database_status", type="string", example="Ошибка подключения к БД"),
     *                      @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="status", type="string", example="error"),
     *                      @OA\Property(property="code", type="string", example="UNKNOWN_ERROR"),
     *                      @OA\Property(property="message", type="string", example="Неизвестная ошибка при подключении к БД."),
     *                      @OA\Property(property="server_status", type="string", example="Активен"),
     *                      @OA\Property(property="database_status", type="string", example="Неизвестная ошибка"),
     *                      @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *                  )
     *              }
     *          )
     *      )
     *  )
     *
     *
     * @return JsonResponse Ответ с состоянием сервера и БД.
     */
    public function checkActivity(): \Illuminate\Http\JsonResponse
    {
        Log::info("Я в checkActivity");
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
     * @OA\Post(
     *     path="/api/signup",
     *     summary="Регистрация нового пользователя",
     *     description="Метод регистрирует нового пользователя, создаёт UUID, хэширует пароль и возвращает данные пользователя с токеном авторизации.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные для регистрации",
     *         @OA\JsonContent(
     *             required={"login", "email", "password", "password_confirmation"},
     *             @OA\Property(
     *                 property="login",
     *                 type="string",
     *                 example="user123",
     *                 description="Логин пользователя. Должен быть уникальным и содержать не более 55 символов."
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="user@example.com",
     *                 description="Email пользователя. Должен быть уникальным и соответствовать формату email."
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="12345678A!",
     *                 description="Пароль пользователя. Должен содержать минимум 8 символов, включая буквы и символы."
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 format="password",
     *                 example="12345678A!",
     *                 description="Подтверждение пароля. Должно совпадать с полем password."
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешная регистрация",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id_user", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="login", type="string", example="user123"),
     *                 @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-13T23:23:19.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-13T23:23:19.000000Z")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|abcdef1234567890"),
     *             @OA\Property(property="expires_at", type="string", format="date-time", example="2025-04-14T12:30:00Z"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="login", type="array",
     *                     @OA\Items(type="string", example="The login field is required.")
     *                 ),
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="The email field is required.")
     *                 ),
     *                 @OA\Property(property="password", type="array",
     *                     @OA\Items(type="string", example="The password field is required.")
     *                 ),
     *                 @OA\Property(property="password_confirmation", type="array",
     *                     @OA\Items(type="string", example="The password confirmation field is required.")
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Internal Server Error"),
     *             @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *         ),
     *     ),
     * )
     *
     * @param SignupRequest $request Валидированный запрос с данными для регистрации.
     * @return JsonResponse Ответ с данными пользователя и токеном.
     */
    public function signup(SignupRequest $request): JsonResponse
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

        //$expiresAt = now()->addMinutes(1);
        //$expiresAt = now()->addDays(2);

        $token = $user->createToken('main', ['*'], now()->addDays(2))->plainTextToken;
        //$token = $user->createToken('main', ['*'], now()->addMinutes(1))->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }


    /**
     * Авторизует пользователя.
     *
     * Проверяет переданные данные через валидированный запрос, выполняет попытку входа.
     * В случае успешной аутентификации создаёт токен авторизации
     * и возвращает пользователя с токеном. При ошибке возвращает сообщение.
     *
     * @OA\Post(
     *     path="/api/login",
     *     summary="Авторизация пользователя",
     *     description="Метод проверяет данные пользователя и в случае успешной аутентификации возвращает токен.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные для входа",
     *         @OA\JsonContent(
     *             required={"login", "password"},
     *             @OA\Property(
     *                 property="login",
     *                 type="string",
     *                 example="user123",
     *                 description="Логин пользователя. Должен существовать в системе."
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="12345678A!",
     *                 description="Пароль пользователя. Должен соответствовать сохранённому в системе."
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Успешная авторизация",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="user",
     *                  type="object",
     *                  @OA\Property(property="id_user", type="string", example="b9649790-f696-4aa0-9d40-dcfdddfbd9ec"),
     *                  @OA\Property(property="login", type="string", example="qwerty"),
     *                  @OA\Property(property="email", type="string", example="qwerty@example.com"),
     *                  @OA\Property(property="created_quizzes_counter", type="integer", example=0),
     *                  @OA\Property(property="taken_quizzes_counter", type="integer", example=0),
     *                  @OA\Property(property="created_at", type="string", example="2025-04-13T21:28:50.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", example="2025-04-13T21:28:50.000000Z")
     *              ),
     *              @OA\Property(property="token", type="string", example="20|ySOWySAojPSFajGYD58PKtJcgLctMvZRYhwjuerG61e0c9b5"),
     *              @OA\Property(property="expires_at", type="string", example="2025-04-13T23:14:16.000000Z")
     *          )
     *      ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Логин или пароль не верны."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="login", type="array",
     *                     @OA\Items(type="string", example="The login field is required.")
     *                 ),
     *                 @OA\Property(property="password", type="array",
     *                     @OA\Items(type="string", example="The password field is required.")
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Произошла внутренняя ошибка сервера."),
     *             @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *         )
     *     )
     * )
     *
     * @param LoginRequest $request Валидированный запрос с данными для входа.
     * @return JsonResponse Ответ с данными пользователя, токеном и временем истечения токена или сообщение об ошибке.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        Log::info("Я в методе login");
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Логин или пароль не верны.'
            ], 422);
        }

        /** @var User $user */
        $user = Auth::user();
        // Создание токена с указанием времени окончания
        $tokenResult = $user->createToken('main', ['*'], now()->addDays(2));
        //$tokenResult = $user->createToken('main', ['*'], now()->addMinutes(1));
        $token = $tokenResult->plainTextToken;

        // Находим запись токена в базе, чтобы взять expires_at
        $tokenModel = $user->tokens()->latest()->first(); // последний созданный токен
        $expiresAt = optional($tokenModel->expires_at)->toISOString(); // ISO-строка или null

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }


    /**
     * Выход пользователя из системы.
     *
     * Метод удаляет текущий токен доступа пользователя, если он авторизован.
     * В случае успешного выполнения возвращает пустой ответ с кодом 204.
     * Если пользователь не авторизован, возвращает ошибку 401.
     *
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Выход пользователя из системы",
     *     description="Метод удаляет текущий токен доступа пользователя, завершая сессию.",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *          response=200,
     *          description="Успешный выход",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Успешный выход")
     *          )
     *      ),
     *     @OA\Response(
     *         response=401,
     *         description="Ошибка авторизации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Не авторизован."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Произошла внутренняя ошибка сервера."),
     *             @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *         )
     *     )
     * )
     *
     * @param Request $request Запрос, содержащий данные пользователя.
     * @return JsonResponse Пустой ответ с кодом 204 или сообщение об ошибке.
     */
    public function logout(Request $request): JsonResponse
    {
        Log::info("Я в методе logout");
        /** @var User $user */
        $user = $request->user();
        $user?->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Успешный выход'
        ], 200);
    }


    /**
     * Получает информацию о текущем аутентифицированном пользователе.
     *
     * Возвращает данные пользователя и сообщение о действительности токена.
     *
     * @OA\Get(
     *     path="/api/user",
     *     summary="Получение информации о пользователе",
     *     description="Метод возвращает данные текущего аутентифицированного пользователя и сообщение о действительности токена.",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Успешное получение данных",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Иван Иванов"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *             ),
     *             @OA\Property(property="message", type="string", example="Токен действителен"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Необходима авторизация."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Произошла внутренняя ошибка сервера."),
     *             @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *         )
     *     )
     * )
     *
     * @param Request $request Запрос, содержащий аутентифицированного пользователя.
     * @return JsonResponse Ответ в формате JSON с данными пользователя и сообщением.
     */
    public function getUser(Request $request): JsonResponse
    {
        $user = $request->user();

        // Получаем текущий токен из базы
        $token = $user->tokens()->where('id', $user->currentAccessToken()->id)->first();

        if (!$token || $token->expires_at->isPast()) {
            return response()->json(['message' => 'Token expired'], 401);
        }

        return response()->json([
            'user' => $user,
            'message' => 'Токен действителен'
        ]);
    }




    /**
     * Проверяет действительность токена и, если необходимо, продлевает его.
     */
//    public function checkAndExtendToken(Request $request): JsonResponse
//    {
//        echo "==> Метод checkAndExtendToken вызван\n";
//
//        $user = $request->user();
//        if (!$user) {
//            echo "==> Пользователь не найден\n";
//            return response()->json(['message' => 'Unauthorized'], 401);
//        }
//
//        echo "==> Пользователь найден: {$user->id}\n";
//
//        // Просто создаем новый токен без условий, чтобы проверить, сработает ли вообще
//        $tokenResult = $user->createToken('main', ['*'], now()->addMinutes(30));
//
//        echo "==> Новый токен создан. expires_at: " . $tokenResult->accessToken->expires_at . "\n";
//
//        return response()->json([
//            'token' => $tokenResult->plainTextToken,
//            'message' => 'Токен создан (тест)'
//        ]);
//    }


    /**
     * Увеличивает счетчик созданных викторин для пользователя.
     *
     * Принимает логин пользователя, находит его в системе и увеличивает счетчик созданных викторин.
     * Возвращает обновленные данные пользователя и сообщение об успешном обновлении.
     *
     * @OA\Post(
     *     path="/api/increment-created-quizzes-counter",
     *     summary="Увеличение счетчика созданных викторин",
     *     description="Метод принимает логин пользователя, находит его в системе и увеличивает счетчик созданных викторин.",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные для обновления счетчика",
     *         @OA\JsonContent(
     *             required={"login"},
     *             @OA\Property(
     *                 property="login",
     *                 type="string",
     *                 example="k12345a",
     *                 description="Логин пользователя, для которого нужно увеличить счетчик."
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное обновление счетчика",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="login", type="string", example="ivan_2025"),
     *                 @OA\Property(property="created_quizzes_counter", type="integer", example=5),
     *             ),
     *             @OA\Property(property="message", type="string", example="Счетчик созданных викторин успешно обновлен"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Пользователь не найден",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Пользователь не найден"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Неверные данные."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="login", type="array",
     *                     @OA\Items(type="string", example="The login field is required.")
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Произошла внутренняя ошибка сервера."),
     *             @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *         )
     *     )
     * )
     *
     * @param Request $request Запрос, содержащий логин пользователя.
     * @return JsonResponse Ответ с обновленными данными пользователя или сообщение об ошибке.
     */
    public function incrementCreatedQuizzesCounter(Request $request): JsonResponse
    {
        // Валидация входящих данных
        $request->validate([
            'login' => 'required|string',
        ]);

        // Получаем id_user из запроса
        $login = $request->input('login');

        // Находим пользователя по id_user
        $user = User::where('login', $login)->first();

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
