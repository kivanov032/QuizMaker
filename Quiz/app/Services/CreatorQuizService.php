<?php

namespace App\Services;

use App\Helpers\CreatorQuizHelper;
use App\Http\Requests\CreateQuizRequest;
use App\Jobs\NotifyUserServerAboutUserQuiz;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Ramsey\Uuid\Uuid;

class CreatorQuizService
{
    private $creatorQuizHelper;

    public function __construct(CreatorQuizHelper $creatorQuizHelper)
    {
        $this->creatorQuizHelper = $creatorQuizHelper;
    }

    //Проверка связи с бд
    public function checkActivity(): \Illuminate\Http\JsonResponse
    {
        Log::info("Я в checkConnectionWithDB");
        $serverStatus = 'Активен';
        try {
            DB::connection()->getPdo();
            return response()->json([
                'status' => 'success',
                'message' => 'Сервер активен, соединение с БД успешно установлено.',
                'server_status' => $serverStatus,
                'database_status' => 'Подключение к БД успешно',
            ], 200);
        } catch (\PDOException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'DB_CONNECTION_ERROR',
                'message' => 'Ошибка подключения к БД.',
                'server_status' => $serverStatus,
                'database_status' => 'Ошибка подключения к БД',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'UNKNOWN_ERROR',
                'message' => 'Неизвестная ошибка при подключении к БД.',
                'server_status' => $serverStatus,
                'database_status' => 'Неизвестная ошибка',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    //Анализ викторины
    public function searchQuizErrors(Request $request): \Illuminate\Http\JsonResponse
    {
        // Получаем данные из запроса
        $data = $request->all();
        $questions = $data['questions']; // Массив вопросов
        $quizName = $data['quizName'] ?? ''; // Название викторины; если quizName отсутствует или null, используем пустую строку

        Log::info("Полученные данные в метод searchQuizErrors:", $data); // Логирование данных полученных данных

        $questions = CreatorQuizHelper::cleanEmptyFields($questions); //Обращение фактически пустых строк в null
        $cosmetic_errors = CreatorQuizHelper::checkDataForCosmeticErrors($questions); //Формирование списка косметических ошибок
        $questions = CreatorQuizHelper::trimQuestions($questions); //'Trim' полей вопросов
        $minor_errors = CreatorQuizHelper::checkDataForMinorErrors($questions); //Формирование списка несущественных ошибок

        $questions = CreatorQuizHelper::filterQuestions($questions, false); // Фильтрация страничек вопросов
        $questions = CreatorQuizHelper::filterAnswers($questions); // Фильтрация вариантов ответов вопросов

        $logical_errors = CreatorQuizHelper::checkDataForLogicalErrors($questions); //Формирование списка логических ошибок
        $critical_errors = CreatorQuizHelper::checkDataForCriticalErrors($questions); //Формирование списка критических ошибок
        $name_quiz_errors = CreatorQuizHelper::checkNameQuizForErrors($quizName); //Формирование списка ошибок названия викторины

        // Ответ клиенту в виде ошибок викторины (5 видов)
        return response()->json([
            'cosmetic_errors' => $cosmetic_errors,
            'minor_errors' => $minor_errors,
            'critical_errors' => $critical_errors,
            'logical_errors' => $logical_errors,
            'name_quiz_errors' => $name_quiz_errors
        ], 200);
    }


    //Коррекция викторины
    public function fixQuizErrors(Request $request): \Illuminate\Http\JsonResponse
    {
        // Получаем данные из запроса
        $data = $request->all();
        $questions = $data['questions']; // Массив вопросов
        $quizName = $data['quizName'] ?? ''; // Название викторины; если quizName отсутствует или null, используем пустую строку
        $errors = $data['errors'];       // Массив меток на исправление ошибок
        $searchQuizErrors_flag = $data['searchQuizErrors_flag']; // Метка на возвращение ошибок

        Log::info("Полученные данные в метод fixQuizErrors:", $data); // Логирование данных полученных данных

        $questions = CreatorQuizHelper::cleanEmptyFields($questions);

        // Обработка косметических ошибок
        if ($errors['cosmeticErrors']) {
            $questions = CreatorQuizHelper::trimQuestions($questions);
        }

        // Обработка несущественных ошибок
        if ($errors['minorErrors']) {
            if (count($questions) > 1) {
                $questions = CreatorQuizHelper::filterQuestions($questions, true);
            }
            $questions = CreatorQuizHelper::filterAnswers($questions);
        }

        // Обработка логических ошибок
        if ($errors['logicalErrors']) {
            $questions = CreatorQuizHelper::fixLogicalErrors($questions);
        }

        // Обработка косметических ошибок названия викторины
        if ($errors['cosmeticErrorQuizName']) {
            $quizName = CreatorQuizHelper::trimString($quizName);
        }

        // Вызов анализа ошибок (при метке = true)
        if ($searchQuizErrors_flag) {
            $requestForErrors = new Request([
                'questions' => $questions,
                'quizName' => $quizName,
            ]);

            $errorResponse = $this->searchQuizErrors($requestForErrors);
            $errorsData = json_decode($errorResponse->getContent(), true); //Получаем новые данные по ошибкам викторины

            // Объединяем ошибки в одну переменную
            $errors = [
                'cosmetic_errors' => $errorsData['cosmetic_errors'],
                'minor_errors' => $errorsData['minor_errors'],
                'critical_errors' => $errorsData['critical_errors'],
                'logical_errors' => $errorsData['logical_errors'],
                'name_quiz_errors' => $errorsData['name_quiz_errors'],
            ];

            // Ответ клиенту в виде исправленных данных по викторине, по названию викторины и ошибок викторины
            return response()->json([
                'questions' => $questions,
                'quizName' => $quizName,
                'errors' => $errors,
            ], 200);
        } else {

            // Ответ клиенту в виде исправленных данных по викторине и по названию викторины
            return response()->json([
                'questions' => $questions,
                'quizName' => $quizName
            ], 200);
        }
    }


    // Предварительная обработка данных викторины и последующее занесение её в бд (+ занесение )
    public function createQuiz(CreateQuizRequest $request): \Illuminate\Http\JsonResponse
    {
        // Получаем данные из запроса
        $data = $request->all();
        $questions = $data['questions']; // Массив вопросов
        $quizName = $data['quizName'] ?? ''; // Название викторины; если quizName отсутствует или null, используем пустую строку
        $errors = $data['errors']; // Массив меток на исправление ошибок
        $id_user = $data['id_user']; // ID пользователя

        Log::info("Полученные данные в метод createQuizWithQuestions:", $data);

        // Исправление ошибок, если они есть
        if ($errors !== null) {
            // Вызов метода для поиска ошибок
            $requestForFixErrors = new Request([
                'questions' => $questions,
                'quizName' => $quizName,
                'errors' => $errors,
                'searchQuizErrors_flag' => false,
            ]);

            $response = $this->fixQuizErrors($requestForFixErrors);
            $fixedData = json_decode($response->getContent(), true); // Получаем откорректированные данные по викторине

            Log::info("Полученные данные в метод createQuizWithQuestions после исправления ошибок:", $fixedData);

            // Обновляем вопросы и название викторины
            $questions = $fixedData['questions'];
            $quizName = $fixedData['quizName'] ?? ''; // Название викторины; если quizName отсутствует или null, используем пустую строку

            // Обновляем данные в запросе
            $request->merge([
                'quizName' => $quizName,
                'questions' => $questions,
            ]);
        }

        // Валидируем данные после исправления ошибок
        $validatedData = $request->validate($request->rules());

        try {
            // Вызов метода для записи викторины в базу данных
            $this->saveQuizToDatabase($quizName, $questions);

            // Отправка данных викторины на внешний сервер в фоновом режиме
            Queue::push(new NotifyUserServerAboutUserQuiz([
                'id_user' => $id_user
            ]));

            // Возвращаем успешный ответ
            return response()->json(['status' => 'success', 'operation_index' => 1], 201);
        } catch (\Exception $e) {
            Log::error('Ошибка при создании викторины: ' . $e->getMessage());

            // Возвращаем ошибку
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при создании викторины.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Сохранение викторины в бд
    private function saveQuizToDatabase(string $quizName, array $questions): void
    {
        DB::transaction(function () use ($quizName, $questions) {
            // Занесение в бд название викторины (табл. quizzes)
            $quiz = Quiz::create([
                'id_quiz' => Uuid::uuid4()->toString(),
                'name_quiz' => $quizName,
                'is_ready' => true,
                'id_user' => null,
            ]);
            $id_quiz = $quiz->id_quiz;

            // Занесение в бд вопросов викторины (табл. quiz_question_answers)
            foreach ($questions as $question) {
                Log::info("question: ", $question);
                QuizQuestion::create([
                    'id_quiz_question_answers' => Uuid::uuid4()->toString(),
                    'text_question' => $question['question'],
                    'correct_option' => $question['answers'][$question['correctAnswerIndex']],
                    'wrong_option' => array_values(array_filter($question['answers'], function($answer) use ($question) {
                        return $answer !== $question['answers'][$question['correctAnswerIndex']];
                    })),
                    'id_quiz' => $id_quiz,
                ]);
            }
        });
    }

}
