<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuizRequest extends FormRequest
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
            'quizName' => 'required|string|max:200|min:5', // Название викторины обязательно
            'questions' => 'required|array', // Вопросы обязательны и должны быть массивом
            'questions.*.question' => 'required|string|max:350', // Текст вопроса обязателен
            'questions.*.answers' => 'required|array', // Ответы обязательны и должны быть массивом
            'questions.*.correctAnswerIndex' => 'required|integer|min:0', // Индекс правильного ответа обязателен
        ];
    }

    public function validateResolved(): void
    {
        // Ничего не делаем, чтобы валидация не выполнялась автоматически
    }
}
