<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Validation\ValidationException;

    /**
     * @OA\Components(
     *     @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         type="http",
     *         scheme="bearer",
     *         bearerFormat="JWT",
     *         in="header",
     *         name="Authorization",
     *         description="Введите токен в формате: Bearer {your_token}"
     *     )
     * )
     */
class AuthController extends Controller
{

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }


    /**
     * Проверяет введённые данные при регистрации.
     *
     * Метод принимает данные для регистрации, валидирует их и возвращает успешный ответ
     * с сообщением, если все данные корректны.
     *
     * @OA\Post(
     *     path="/api/validate-signup",
     *     summary="Проверка данных для регистрации",
     *     description="Метод проверяет корректность данных, предоставленных пользователем при регистрации.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные для проверки регистрации",
     *         @OA\JsonContent(
     *             required={"login", "email", "password", "password_confirmation"},
     *             @OA\Property(property="login", type="string", example="user123"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678A!"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="12345678A!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Данные прошли проверку",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Данные прошли проверку.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Поле login обязательно для заполнения."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="login", type="array",
     *                     @OA\Items(type="string", example="Поле login обязательно для заполнения.")
     *                 ),
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="Поле email обязательно для заполнения.")
     *                 ),
     *                 @OA\Property(property="password", type="array",
     *                     @OA\Items(type="string", example="Поле password обязательно для заполнения.")
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message": "Internal Server Error",
     *                 "error": "SQLSTATE[HY000] [2002] Connection refused"
     *             }
     *         )
     *     )
     * )
     *
     * @param SignupRequest $request Валидированный запрос с данными для регистрации.
     * @return JsonResponse Ответ с сообщением о результатах валидации данных.
     */
    public function validateSignup(SignupRequest $request): JsonResponse
    {
        return response()->json($this->authService->validateSignup($request));
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
     *             oneOf={
     *                 @OA\Schema(
     *                     description="Успешный пример запроса",
     *                     required={"login", "email", "password", "password_confirmation"},
     *                     @OA\Property(property="login", type="string", example="user123"),
     *                     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *                     @OA\Property(property="password", type="string", format="password", example="12345678A!"),
     *                     @OA\Property(property="password_confirmation", type="string", format="password", example="12345678A!")
     *                 ),
     *                 @OA\Schema(
     *                     description="Пример с ошибками валидации",
     *                     required={"login", "email", "password", "password_confirmation"},
     *                     @OA\Property(property="login", type="string", example=""),
     *                     @OA\Property(property="email", type="string", format="email", example="user"),
     *                     @OA\Property(property="password", type="string", format="password", example="12345678A!"),
     *                     @OA\Property(property="password_confirmation", type="string", format="password", example="")
     *                 )
     *             }
     *         )
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
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Поле login обязательно для заполнения. (and 1 more error)"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="login", type="array",
     *                     @OA\Items(type="string", example="Поле login уже занято.")
     *                 ),
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="Поле email уже занято.")
     *                 ),
     *                 @OA\Property(property="password", type="array",
     *                     @OA\Items(type="string", example="Поле password обязательно для заполнения.")
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message": "Internal Server Error",
     *                 "error": "SQLSTATE[HY000] [2002] Connection refused"
     *             }
     *         )
     *     )
     * )
     *
     * @param SignupRequest $request Валидированный запрос с данными для регистрации.
     * @return JsonResponse Ответ с данными пользователя и токеном.
     */
    public function signup(SignupRequest $request): JsonResponse
    {
        return response()->json($this->authService->signup($request));
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
     *          )
     *      ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Поле login обязательно для заполнения. (and 1 more error)"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="login", type="array",
     *                     @OA\Items(type="string", example="Поле login обязательно для заполнения.")
     *                 ),
     *                 @OA\Property(property="password", type="array",
     *                     @OA\Items(type="string", example="Поле password обязательно для заполнения.")
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *          response=500,
     *          description="Внутренняя ошибка сервера",
     *          @OA\JsonContent(
     *              type="object",
     *              example={
     *                  "message": "Internal Server Error",
     *                  "error": "SQLSTATE[HY000] [2002] Connection refused"
     *              }
     *          )
     *      )
     * )
     *
     * @param LoginRequest $request Валидированный запрос с данными для входа.
     * @return JsonResponse Ответ с данными пользователя, токеном и временем истечения токена или сообщение об ошибке.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            return response()->json($this->authService->login($request));
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
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
     *           response=500,
     *           description="Внутренняя ошибка сервера",
     *           @OA\JsonContent(
     *               type="object",
     *               example={
     *                   "message": "Internal Server Error",
     *                   "error": "SQLSTATE[HY000] [2002] Connection refused"
     *               }
     *           )
     *       )
     * )
     *
     * @param Request $request Запрос, содержащий данные пользователя.
     * @return JsonResponse Пустой ответ с кодом 200 или сообщение об ошибке.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request);
        return response()->json(['message' => 'Успешный выход']);
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
     *                 @OA\Property(property="id_user", type="string", format="uuid", example="b9649790-f696-4aa0-9d40-dcfdddfbd9ec"),
     *                 @OA\Property(property="login", type="string", example="qwerty"),
     *                 @OA\Property(property="email", type="string", example="qwerty@example.com"),
     *                 @OA\Property(property="created_quizzes_counter", type="integer", example=0),
     *                 @OA\Property(property="taken_quizzes_counter", type="integer", example=0),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-13T21:28:50.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-13T21:28:50.000000Z"),
     *             ),
     *             @OA\Property(property="message", type="string", example="Токен действителен"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Не авторизован.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message": "Internal Server Error",
     *                 "error": "SQLSTATE[HY000] [2002] Connection refused"
     *             }
     *         )
     *     )
     * )
     *
     * @param Request $request Запрос, содержащий аутентифицированного пользователя.
     * @return JsonResponse Ответ в формате JSON с данными пользователя и сообщением.
     */
    public function getUser(Request $request): JsonResponse
    {
        try {
            return response()->json($this->authService->getUser($request));
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

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
