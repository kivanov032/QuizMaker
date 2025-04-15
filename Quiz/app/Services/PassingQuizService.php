<?php

namespace App\Services;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PassingQuizService
{


    /**
     * Ищет викторины по названию.
     *
     * Принимает строку поиска, выполняет регистронезависимый поиск викторин по названию.
     * Возвращает список найденных викторин или сообщение об ошибке.
     *
     * @OA\Post(
     *     path="/api/search-quiz",
     *     summary="Поиск викторин по названию",
     *     description="Метод выполняет поиск викторин по части названия (регистронезависимый).",
     *     tags={"PassingQuiz"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Параметры поиска",
     *         @OA\JsonContent(
     *             required={"name_quiz"},
     *             @OA\Property(
     *                 property="name_quiz",
     *                 type="string",
     *                 example="Тест по программированию",
     *                 description="Название или часть названия викторины для поиска"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный поиск",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="quizzes",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id_quiz", type="integer", example=1),
     *                     @OA\Property(property="name_quiz", type="string", example="История Древнего мира"),
     *                     @OA\Property(property="description", type="string", example="Тест по древней истории"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-10T12:00:00Z"),
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Найдены следующие викторины"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Викторины не найдены",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Викторины не найдены"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Параметр поиска обязателен"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="name_quiz", type="array",
     *                     @OA\Items(type="string", example="The name_quiz field is required.")
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Произошла внутренняя ошибка сервера"),
     *             @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *         )
     *     )
     * )
     *
     * @param Request $request Валидированный запрос с параметром поиска
     * @return JsonResponse Ответ с найденными викторинами или сообщением об ошибке
     */
    public function searchQuiz(Request $request): JsonResponse
    {
        $request->validate([
            'name_quiz' => 'required|string',
        ]);

        $nameQuiz = $request->input('name_quiz');
        $quizzes = Quiz::where('name_quiz', 'ILIKE', "%{$nameQuiz}%")->get();

        if ($quizzes->isEmpty()) {
            return response()->json(['message' => 'Викторины не найдены'], 404);
        }

        return response()->json([
            'quizzes' => $quizzes,
            'message' => 'Найдены следующие викторины',
        ]);
    }

}
