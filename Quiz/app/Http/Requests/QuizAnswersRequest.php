<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuizAnswersRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь делать этот запрос.
     */
    public function authorize(): bool
    {
        return true; // Разрешить всем
    }

    /**
     * Получить правила валидации, применяемые к запросу.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'userAnswers' => 'array',
            'quizQuestions' => 'required|array',
            'login' => 'string',
        ];
    }
}
