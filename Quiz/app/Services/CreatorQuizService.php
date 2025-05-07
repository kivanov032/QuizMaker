<?php

namespace App\Services;

use App\Helpers\CreatorQuizHelper;
use App\Http\Requests\QuizRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Jobs\NotifyUserServerAboutUserQuiz;
use Illuminate\Support\Facades\Queue;

class CreatorQuizService
{

    // Проверка активности сервера и подключения к базе данных.
    public function checkActivity(): JsonResponse
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
        } catch (Exception $e) {
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


    // Анализ викторины на наличие ошибок.
    public function searchQuizErrors(Request $request): JsonResponse
    {
        // Получаем данные из запроса
        $data = $request->all();
        $questions = $data['questions']; // Массив вопросов
        $quizName = $data['quizName'] ?? ''; // Название викторины; если quizName отсутствует или null, используем пустую строку

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


    // Исправление ошибок в викторине
    public function fixQuizErrors(Request $request): JsonResponse
    {
        // Получаем данные из запроса
        $data = $request->all();
        $questions = $data['questions']; // Массив вопросов
        $quizName = $data['quizName'] ?? ''; // Название викторины; если quizName отсутствует или null, используем пустую строку
        $errors = $data['errors'];       // Массив меток на исправление ошибок
        $searchQuizErrors_flag = $data['searchQuizErrors_flag']; // Метка на возвращение ошибок

        $questions = CreatorQuizHelper::cleanEmptyFields($questions); //Обращение фактически пустых строк в null

        // Обработка косметических ошибок
        if ($errors['cosmeticErrors']) {
            $questions = CreatorQuizHelper::trimQuestions($questions);
        }

        // Обработка несущественных ошибок
        if ($errors['minorErrors']) {
            // Если в викторине одна пустая страничка вопроса, то мы не удаляем её
            if (count($questions) > 1) {
                $questions = CreatorQuizHelper::filterQuestions($questions, true); // Удаление пустых страничек вопросов
            }
            $questions = CreatorQuizHelper::filterAnswers($questions); // Удаление пустых строчек под варианты ответов
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
            // Формирование запроса для метода searchQuizErrors
            $requestForErrors = new Request([
                'questions' => $questions,
                'quizName' => $quizName,
            ]);

            $errorResponse = $this->searchQuizErrors($requestForErrors); // Анализ викторины на наличие ошибок
            $errorsData = json_decode($errorResponse->getContent(), true); //Получаем новые данные по ошибкам викторины

            // Объединяем ошибки в одну переменную
            $errors = [
                'cosmetic_errors' => $errorsData['cosmetic_errors'],
                'minor_errors' => $errorsData['minor_errors'],
                'critical_errors' => $errorsData['critical_errors'],
                'logical_errors' => $errorsData['logical_errors'],
                'name_quiz_errors' => $errorsData['name_quiz_errors'],
            ];

            return response()->json([
                'questions' => $questions, // Обновлённые вопросы викторины
                'quizName' => $quizName, // Обновлённое название викторины
                'errors' => $errors // Оставшиеся ошибки викторины
            ], 200);
        } else {
            return response()->json([
                'questions' => $questions, // Обновлённые вопросы викторины
                'quizName' => $quizName // Обновлённое название викторины
            ], 200);
        }
    }


    // Занесение викторины в бд
    public function createQuiz(QuizRequest $request): JsonResponse
    {
        // Получаем данные из запроса
        $data = $request->all();
        $questions = $data['questions']; // Массив вопросов
        $quizName = $data['quizName'] ?? ''; // Название викторины; если quizName отсутствует или null, используем пустую строку
        $errors = $data['errors']; // Массив меток на исправление ошибок
        $login = $data['login']; // Логин пользователя

        // Исправление ошибок, если они есть
        if ($errors !== null) {
            // Формирование запроса для метода fixQuizErrors
            $requestForFixErrors = new Request([
                'questions' => $questions, // Вопросы викторины
                'quizName' => $quizName, // Название викторины
                'errors' => $errors, // Ошибки викторины
                'searchQuizErrors_flag' => false, // Метка false на анализ викторины на ошибки
            ]);

            $response = $this->fixQuizErrors($requestForFixErrors); // Исправление ошибок в викторине.
            $fixedData = json_decode($response->getContent(), true); // Получаем откорректированные данные по викторине

            // Обновляем вопросы и название викторины
            $questions = $fixedData['questions']; // Обновлённые вопросы викторины
            $quizName = $fixedData['quizName'] ?? ''; // Обновлённое название викторины

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
            CreatorQuizHelper::saveQuizToDatabase($quizName, $questions, $login);

//            // Отправка данных викторины на внешний сервер в фоновом режиме
//            Queue::push(new NotifyUserServerAboutUserQuiz([
//                'login' => $login
//            ]));


            // Использование сервиса Kafka для оповещения микросервиса User о том,
            // что викторина создана таким-то пользователем
//            try {
//                KafkaService::publish(
//                    'localhost',
//                    'quiz_created',
//                    ['login' => $login]
//                );
//            } catch (Exception $kafkaException) {
//                Log::error('Ошибка Kafka: ' . $kafkaException->getMessage());
//            }

            dispatch(function () use ($login) {
                try {
                    KafkaService::publish(
                        'localhost',
                        'quiz_created',
                        ['login' => $login]
                    );
                } catch (Exception $e) {
                    Log::error('Kafka error: ' . $e->getMessage());
                }
            });

            // Возвращаем успешный ответ
            return response()->json(['status' => 'success', 'operation_index' => 1], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при создании викторины.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
