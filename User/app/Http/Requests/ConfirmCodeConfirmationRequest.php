<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmCodeConfirmationRequest extends FormRequest
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
            'input_code' => 'required|digits:6',
            'email' => 'required|email'
        ];
    }

    /**
     * Кастомные сообщения об ошибках.
     */
    public function messages(): array
    {
        return [
            // Код подтверждения
            'input_code.required' => 'Поле кода подтверждения обязательно для заполнения',
            'input_code.digits' => 'Код подтверждения должен содержать ровно 6 цифр',


            // Email
            'email.required' => 'Поле логина обязательно для заполнения',
            'email.email' => 'Введите корректный email адрес',
        ];
    }

    /**
     * Кастомные названия полей.
     */
    public function attributes(): array
    {
        return [
            'input_code' => 'Код подтверждения',
            'email' => 'Email',
        ];
    }
}
