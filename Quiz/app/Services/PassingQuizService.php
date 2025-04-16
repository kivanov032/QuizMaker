<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizQuestion;
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
     *             required={"quizName"},
     *             @OA\Property(
     *                 property="quizName",
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
     *                 @OA\Property(property="quizName", type="array",
     *                     @OA\Items(type="string", example="The quizName field is required.")
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
            'quizName' => 'required|string',
        ]);

        $quizName = $request->input('quizName');
        Log::info("Полученное название викторины в методе downloadQuizQuestion:", ['quizName' => $quizName]); // Логирование данных полученных данных
        $quizzes = Quiz::where('name_quiz', 'ILIKE', "%{$quizName}%")
            ->orderBy('name_quiz')      // Сортировка по названию викторины (по возрастанию)
            ->orderBy('login_user')     // Затем по логину пользователя
            ->orderBy('created_at')     // Затем по дате создания
            ->orderBy('was_taken')      // И наконец по статусу прохождения
            ->get();


        if ($quizzes->isEmpty()) {
            return response()->json(['message' => 'Викторины не найдены'], 404);
        }

        return response()->json([
            'quizzes' => $quizzes,
            'message' => 'Найдены следующие викторины',
        ], 200);
    }


    /**
     * Загружает вопросы викторины.
     *
     * Получает UUID викторины, загружает соответствующие вопросы из базы данных,
     * преобразует их структуру (объединяет и перемешивает варианты ответов),
     * сохраняя индекс правильного ответа. Возвращает перемешанные вопросы
     * в стандартизированном формате.
     *
     * @OA\Post(
     *     path="/api/download-quiz-questions",
     *     summary="Загрузка вопросов викторины",
     *     description="Метод возвращает все вопросы для указанной викторины в перемешанном порядке с перемешанными вариантами ответов.",
     *     tags={"PassingQuiz"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="UUID викторины",
     *         @OA\JsonContent(
     *             required={"id_quiz"},
     *             @OA\Property(
     *                 property="id_quiz",
     *                 type="string",
     *                 format="uuid",
     *                 example="593d3cf4-4aa7-438a-9e36-f7733cfcf473",
     *                 description="Уникальный идентификатор викторины в формате UUID"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный запрос",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="questions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1, description="Порядковый номер вопроса"),
     *                     @OA\Property(property="question", type="string", example="What is your name?", description="Текст вопроса"),
     *                     @OA\Property(
     *                         property="answers",
     *                         type="array",
     *                         @OA\Items(type="string", example="John"),
     *                         description="Массив перемешанных вариантов ответов"
     *                     ),
     *                     @OA\Property(
     *                         property="correctAnswerIndex",
     *                         type="integer",
     *                         example=0,
     *                         description="Индекс правильного ответа в массиве answers (начинается с 0)"
     *                     ),
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Найдены следующие вопросы викторины"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Вопросы не найдены",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Вопросы викторины не найдены"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="id_quiz", type="array",
     *                     @OA\Items(type="string", example="The id quiz field is required.")
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Произошла внутренняя ошибка сервера."),
     *             @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *         )
     *     )
     * )
     *
     * @param Request $request Валидированный запрос с UUID викторины.
     * @return JsonResponse Ответ с вопросами викторины или сообщением об ошибке.
     */
    public function downloadQuizQuestions(Request $request): JsonResponse
    {
        $request->validate([
            'id_quiz' => 'required|uuid',
        ]);

        $quizID = $request->input('id_quiz');
        Log::info("Полученный id викторины в методе downloadQuizQuestion:", ['quizID' => $quizID]);

        $questions = QuizQuestion::where('id_quiz', $quizID)->get();

        if ($questions->isEmpty()) {
            return response()->json(['message' => 'Вопросы викторины не найдены'], 404);
        }

        $transformedQuestions = $questions->map(function ($question, $index) {
            // Объединение всех вариантов ответов в один массив
            $allAnswers = array_merge([$question->correct_option], $question->wrong_option);

            // Перемешивание ответов с запоминаем позиции правильного
            $correctAnswerValue = $question->correct_option;
            shuffle($allAnswers);

            $correctAnswerIndex = array_search($correctAnswerValue, $allAnswers);

            return [
                'id' => $index + 1,
                'question' => $question->text_question,
                'answers' => $allAnswers,
                'correctAnswerIndex' => $correctAnswerIndex
            ];
        })->shuffle(); // Перемешивание вопросов между собой

        return response()->json([
            'questions' => $transformedQuestions,
            'message' => 'Найдены следующие вопросы викторины',
        ], 200);
    }


    /**
     * Проверяет ответы пользователя на викторину.
     *
     * Принимает ответы пользователя и данные викторины, валидирует их,
     * сравнивает с правильными ответами и возвращает детализированные результаты.
     * Логирует входящие данные для отладки. Включает расширенную статистику:
     * общее количество вопросов, правильные/неправильные ответы, пропущенные вопросы и процент правильных ответов.
     *
     * @OA\Post(
     *     path="/api/check-quiz-answers",
     *     summary="Проверка ответов викторины",
     *     description="Метод проверяет ответы пользователя на соответствие правильным ответам викторины и возвращает детализированный отчет с расширенной статистикой.",
     *     tags={"PassingQuiz"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные для проверки ответов",
     *         @OA\JsonContent(
     *             required={"userAnswers", "quizQuestions", "id_quiz"},
     *             @OA\Property(
     *                 property="userAnswers",
     *                 type="object",
     *                 example={"question1": 0, "question2": 2},
     *                 description="Ассоциативный массив ответов пользователя (ключ - ID вопроса, значение - индекс выбранного ответа)"
     *             ),
     *             @OA\Property(
     *                 property="quizQuestions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="question1"),
     *                     @OA\Property(property="correctAnswerIndex", type="integer", example=1),
     *                     @OA\Property(
     *                         property="answers",
     *                         type="array",
     *                         @OA\Items(type="string", example="Вариант ответа")
     *                     )
     *                 ),
     *                 description="Массив вопросов викторины с правильными ответами"
     *             ),
     *             @OA\Property(
     *                 property="id_quiz",
     *                 type="string",
     *                 format="uuid",
     *                 example="550e8400-e29b-41d4-a716-446655440000",
     *                 description="UUID викторины"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешная проверка ответов",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="results",
     *                 type="object",
     *                 description="Детализированные результаты по каждому вопросу",
     *                 @OA\AdditionalProperties(
     *                     type="object",
     *                     @OA\Property(property="status", type="string", enum={"Right", "Wrong", "Not Entered"}),
     *                     @OA\Property(property="userAnswer", type="string", nullable=true),
     *                     @OA\Property(property="correctAnswer", type="string", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Результаты проверки ответов"),
     *             @OA\Property(
     *                 property="summary",
     *                 type="object",
     *                 description="Расширенная статистика по результатам",
     *                 @OA\Property(property="totalQuestions", type="integer", example=10, description="Общее количество вопросов"),
     *                 @OA\Property(property="correctAnswers", type="integer", example=7, description="Количество правильных ответов"),
     *                 @OA\Property(property="wrongAnswers", type="integer", example=1, description="Количество неправильных ответов"),
     *                 @OA\Property(property="skippedQuestions", type="integer", example=2, description="Количество пропущенных вопросов"),
     *                 @OA\Property(property="scorePercentage", type="number", format="float", example=70.0, description="Процент правильных ответов")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="userAnswers", type="array",
     *                     @OA\Items(type="string", example="The userAnswers field is required.")
     *                 ),
     *                 @OA\Property(property="quizQuestions", type="array",
     *                     @OA\Items(type="string", example="The quizQuestions field is required.")
     *                 ),
     *                 @OA\Property(property="id_quiz", type="array",
     *                     @OA\Items(type="string", example="The id_quiz field is required.")
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Произошла внутренняя ошибка сервера."),
     *             @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *         )
     *     )
     * )
     *
     * @param Request $request Валидированный запрос с ответами пользователя и данными викторины
     * @return JsonResponse Ответ с детализированными результатами проверки и расширенной статистикой
     */
    public function checkQuizAnswers(Request $request): JsonResponse
    {
        $request->validate([
            'userAnswers' => 'array',
            'quizQuestions' => 'required|array',
            'id_quiz' => 'uuid',
        ]);

        $userAnswers = $request->input('userAnswers');
        $quizQuestions = $request->input('quizQuestions');
        $id_quiz = $request->input('id_quiz');
        Log::info("Ответы пользователя в методе checkQuizAnswers:", ['userAnswers' => $userAnswers]); // Логирование данных полученных данных
        Log::info("Данные викторины в методе checkQuizAnswers:", ['quizQuestions' => $quizQuestions]); // Логирование данных полученных данных
        Log::info("ID викторины в методе checkQuizAnswers:", ['id_quiz' => $id_quiz]); // Логирование данных полученных данных
        $results = [];

        // Обработка ответов
        foreach ($quizQuestions as $question) {
            $questionId = $question['id'];
            $userAnswerIndex = $userAnswers[$questionId] ?? null;

            if ($userAnswerIndex === null) {
                $results[$questionId] = [
                    'status' => 'Not Entered',
                    'userAnswer' => null,
                    'correctAnswer' => $question['answers'][$question['correctAnswerIndex']] ?? null
                ];
                continue;
            }

            $isCorrect = $userAnswerIndex == $question['correctAnswerIndex'];

            $results[$questionId] = [
                'status' => $isCorrect ? 'Right' : 'Wrong',
                'userAnswer' => $question['answers'][$userAnswerIndex] ?? null,
                'correctAnswer' => $question['answers'][$question['correctAnswerIndex']] ?? null
            ];
        }

        // Расчет статистики в переменных
        $totalQuestions = count($quizQuestions);
        $correctAnswers = count(array_filter($results, fn($r) => $r['status'] === 'Right'));
        $wrongAnswers = count(array_filter($results, fn($r) => $r['status'] === 'Wrong'));
        $skippedQuestions = count(array_filter($results, fn($r) => $r['status'] === 'Not Entered'));
        $scorePercentage = round($correctAnswers / $totalQuestions * 100, 2);

        return response()->json([
            'results' => $results,
            'message' => 'Результаты проверки ответов',
            'summary' => [
                'totalQuestions' => $totalQuestions,
                'correctAnswers' => $correctAnswers,
                'skippedQuestions' => $skippedQuestions,
                'wrongAnswers' => $wrongAnswers,
                'scorePercentage' => $scorePercentage
            ]
        ], 200);
    }





}
