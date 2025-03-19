<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Регистрирует нового пользователя.
     *
     * Принимает данные из валидированного запроса, создаёт пользователя,
     * генерирует UUID, хэширует пароль, создаёт токен авторизации
     * и возвращает пользователя вместе с токеном.
     *
     * @param SignupRequest $request Валидированный запрос с данными для регистрации.
     * @return \Illuminate\Http\Response Ответ с данными пользователя и токеном.
     */
    public function signup(SignupRequest $request) {
        $data = $request->validated();
        //Log::info('Signup request received', ['data' => $request->all()]);
        $uuid = Str::uuid();
        /** @var User $user */
        $user = User::create([
            'id_user' => $uuid,
            'login' => $data['login'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $token = $user->createToken('main')->plainTextToken;

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
     * @return \Illuminate\Http\Response Ответ с данными пользователя и токеном или сообщение об ошибке.
     */
    public function login(LoginRequest $request) {
        $credentials = $request->validated();
        if (!Auth::attempt($credentials)) {
            return response([
                'message' => 'Provided email address or password is incorrect'
            ], 422);
        }
        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('main')->plainTextToken;
        return response(compact('user', 'token'));
    }

    /**
     * Выполняет выход пользователя.
     *
     * Удаляет текущий access-токен пользователя и возвращает успешный ответ.
     *
     * @param Request $request Запрос, содержащий аутентифицированного пользователя.
     * @return \Illuminate\Http\Response Пустой ответ с кодом 204 (успешный выход).
     */
    public function logout(Request $request) {
        /** @var User $user */
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return response('', 204);
    }
}
