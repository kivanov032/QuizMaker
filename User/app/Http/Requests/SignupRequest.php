<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

class SignupRequest extends BaseValidationRequest
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
            'login' => 'required|string|max:55|unique:users,login',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->symbols()
                    ->max(100)
            ],
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
            'login.max' => 'Логин не должен превышать :max символов',
            'login.unique' => 'Такой логин уже занят',

            // Email
            'email.required' => 'Поле email обязательно для заполнения',
            'email.email' => 'Введите корректный email адрес',
            'email.unique' => 'Такой email уже зарегистрирован',

            // Пароль
            'password.required' => 'Поле пароля обязательно для заполнения',
            'password.confirmed' => 'Пароли не совпадают',
            'password.min' => 'Пароль должен содержать минимум :min символов',
            'password.max' => 'Пароль не должен превышать :max символов',
        ];
    }

    /**
     * Кастомные названия полей.
     */
    public function attributes(): array
    {
        return [
            'login' => 'Логин',
            'email' => 'Email',
            'password' => 'Пароль',
            'password_confirmation' => 'Подтверждение пароля',
        ];
    }

    /**
     * Дополнительная валидация для сложных правил пароля.
     */
    protected function passedValidation()
    {
        $this->ensurePasswordMeetsRequirements();
    }

    private function ensurePasswordMeetsRequirements()
    {
        $password = $this->input('password');

        if (!preg_match('/[A-Za-z]/', $password)) {
            $this->validator->errors()->add(
                'password',
                'Пароль должен содержать буквы'
            );
        }

        if (!preg_match('/[\W_]/', $password)) {
            $this->validator->errors()->add(
                'password',
                'Пароль должен содержать спецсимволы'
            );
        }
    }
}
