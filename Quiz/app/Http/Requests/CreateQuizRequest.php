<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuizRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // или false, если требуется авторизация
    }

    /**
     * Get the validation rules that apply to the request.
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
            'questions.*.correctAnswerIndex' => 'required|integer|min:1', // Индекс правильного ответа обязателен
        ];
    }

    public function validateResolved(): void
    {
        // Ничего не делаем, чтобы валидация не выполнялась автоматически
    }
}
