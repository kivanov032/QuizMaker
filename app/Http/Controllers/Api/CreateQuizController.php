<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreateQuizController extends Controller
{
    public function questions(Request $request)
    {
        // Получаем данные из запроса
        $questions = $request->all();

        // Выводим данные в лог
        Log::info('Полученные вопросы:', $questions);

        // Возвращаем ответ клиенту
        return response()->json([
            'message' => 'Вопросы успешно получены! ',
            'data' => $questions,
        ], 200);
    }
}

