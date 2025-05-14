<?php

namespace App\Services;

use App\Http\Requests\QuizAnswersRequest;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PassingQuizService
{
    // Поиск викторин по названию.
    public function searchQuiz(Request $request): JsonResponse
    {
        $request->validate([
            'quizName' => 'required|string',
        ]);

        $quizName = $request->input('quizName');
        $quizzes = Quiz::where('name_quiz', 'ILIKE', "%{$quizName}%")
            ->orderBy('name_quiz')      // Сортировка по названию викторины (по возрастанию)
            ->orderBy('login_user')     // Сортировка по логину пользователя
            ->orderBy('created_at')     // Сортировка по дате создания
            ->orderBy('was_taken')      // Сортировка по статусу прохождения
            ->get();


        if ($quizzes->isEmpty()) {
            return response()->json(['message' => 'Викторины не найдены'], 404);
        }

        return response()->json([
            'quizzes' => $quizzes,
            'message' => 'Найдены следующие викторины',
        ], 200);
    }


    // Получение вопросов викторины
    public function getQuizQuestions(Request $request): JsonResponse
    {
        $request->validate([
            'id_quiz' => 'required|uuid',
        ]);

        $quizID = $request->input('id_quiz');
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


    // Анализ пройденной викторины
    public function checkQuizAnswers(QuizAnswersRequest $request): JsonResponse
    {
        // Получаем данные из запроса
        $data = $request->all();
        $userAnswers = $data['userAnswers']; // Массив ответов
        $quizQuestions = $data['quizQuestions']; // Массив вопросов
        $login = $data['login']; // Логин пользователя, прошедшего викторину

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
        $correctAnswers = count(array_filter($results, fn ($r) => $r['status'] === 'Right'));
        $wrongAnswers = count(array_filter($results, fn ($r) => $r['status'] === 'Wrong'));
        $skippedQuestions = count(array_filter($results, fn ($r) => $r['status'] === 'Not Entered'));
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
