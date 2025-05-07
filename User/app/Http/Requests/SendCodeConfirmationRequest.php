<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendCodeConfirmationRequest extends FormRequest
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
            'email' => 'required|email'
        ];
    }

    /**
     * Кастомные сообщения об ошибках.
     */
    public function messages(): array
    {
        return [
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
            'email' => 'Email',
        ];
    }
}
