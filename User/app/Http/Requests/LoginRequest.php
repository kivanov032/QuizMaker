<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь делать этот запрос.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации.
     */
    public function rules(): array
    {
        return [
            'login' => 'required|string',
            'password' => 'required',
        ];
    }

    /**
     * Кастомные сообщения об ошибках.
     */
    public function messages(): array
    {
        return [
            // Логин
            'login.required' => 'Поле логина обязательно для заполнения',
            'login.string' => 'Логин должен быть строкой',

            // Пароль
            'password.required' => 'Поле пароля обязательно для заполнения',
        ];
    }

    /**
     * Кастомные названия полей.
     */
    public function attributes(): array
    {
        return [
            'login' => 'Логин',
            'password' => 'Пароль',
        ];
    }
}
