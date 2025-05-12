<?php

namespace App\Services;

use App\Http\Requests\SignupRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    // Проверка данных при регистрации
    public function validateSignup(SignupRequest $request): array
    {
        return ['message' => 'Данные прошли проверку.'];
    }

    // Регистрация нового пользователя.
    public function signup(SignupRequest $request): array
    {
        $data = $request->validated();

        $user = User::create([
            'id_user' => Str::uuid(),
            'login' => $data['login'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $token = $user->createToken('main', ['*'], now()->addDays(2))->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    // Авторизация пользователя
    public function login(LoginRequest $request): array
    {
        if (!Auth::attempt($request->validated())) {
            throw ValidationException::withMessages([
                'login' => 'Логин или пароль не верны.'
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('main', ['*'], now()->addDays(2))->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    // Выход пользователя из системы
    public function logout(Request $request): void
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }
    }

    // Получение информации о текущем аутентифицированном пользователе.
    public function getUser(Request $request): array
    {
        $user = $request->user();
        $token = $user->tokens()->where('id', $user->currentAccessToken()->id)->first();

        if (!$token || $token->expires_at->isPast()) {
            abort(401, 'Token expired');
        }

        return ['user' => $user, 'message' => 'Токен действителен'];
    }
}
