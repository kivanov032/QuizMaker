<?php

namespace App\Services;

use App\Helpers\CreatorQuizHelper;
use App\Http\Requests\CreateQuizRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Junges\Kafka\Facades\Kafka;

use App\Jobs\NotifyUserServerAboutUserQuiz;
use Illuminate\Support\Facades\Queue;

class CreatorQuizService
{

    /**
     * Проверяет активность сервера и подключение к базе данных.
     *
     *
     * @OA\Get(
     *      path="/api/check-activity",
     *      summary="Проверка связи с базой данных",
     *      description="Метод проверяет активность сервера и соединение с базой данных.",
     *      tags={"QuizDatabase"},
     *      @OA\Response(
     *          response=200,
     *          description="Успешное подключение к БД",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="message", type="string", example="Сервер активен, соединение с БД успешно установлено."),
     *              @OA\Property(property="server_status", type="string", example="Активен"),
     *              @OA\Property(property="database_status", type="string", example="Подключение к БД успешно"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Ошибка подключения к БД",
     *          @OA\JsonContent(
     *              oneOf={
     *                  @OA\Schema(
     *                      @OA\Property(property="status", type="string", example="error"),
     *                      @OA\Property(property="code", type="string", example="DB_CONNECTION_ERROR"),
     *                      @OA\Property(property="message", type="string", example="Ошибка подключения к БД."),
     *                      @OA\Property(property="server_status", type="string", example="Активен"),
     *                      @OA\Property(property="database_status", type="string", example="Ошибка подключения к БД"),
     *                      @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="status", type="string", example="error"),
     *                      @OA\Property(property="code", type="string", example="UNKNOWN_ERROR"),
     *                      @OA\Property(property="message", type="string", example="Неизвестная ошибка при подключении к БД."),
     *                      @OA\Property(property="server_status", type="string", example="Активен"),
     *                      @OA\Property(property="database_status", type="string", example="Неизвестная ошибка"),
     *                      @OA\Property(property="error", type="string", example="Сообщение об ошибке"),
     *                  )
     *              }
     *          )
     *      )
     *  )
     *
     *
     * @return JsonResponse Ответ с состоянием сервера и БД.
     */

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


    /**
     * Анализирует викторину на наличие ошибок.
     *
     * Метод принимает данные викторины, включая вопросы и название, и проверяет их на наличие
     * различных типов ошибок: косметических, несущественных, логических, критических и ошибок в названии.
     * Возвращает JSON-ответ с перечнем всех найденных ошибок.
     *
     * @OA\Post(
     *     path="/api/search-quiz-errors",
     *     summary="Анализ викторины на ошибки",
     *     description="Метод проверяет вопросы и название викторины на наличие ошибок и возвращает их список.",
     *     tags={"CreatorQuiz"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные викторины для анализа",
     *         @OA\JsonContent(
     *             required={"questions"},
     *             @OA\Property(
     *                 property="questions",
     *                 type="array",
     *                 description="Массив вопросов викторины",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1, description="Уникальный идентификатор вопроса."),
     *                     @OA\Property(property="question", type="string", example="What is your name?", description="Текст вопроса. Может быть пустым или содержать `null`."),
     *                     @OA\Property(property="answers", type="array", description="Массив вариантов ответов. Может содержать пустые строки или `null`.",
     *                         @OA\Items(type="string", example="John")
     *                     ),
     *                     @OA\Property(property="correctAnswerIndex", type="integer", example=0, description="Индекс правильного ответа в массиве answers."),
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="quizName",
     *                 type="string",
     *                 example="Тест по программированию",
     *                 description="Название викторины. Если отсутствует, используется пустая строка."
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный анализ викторины",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="cosmetic_errors",
     *                 type="array",
     *                 description="Список косметических ошибок. Каждая ошибка содержит информацию о вопросе и типе ошибки.",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id_question", type="integer", example=1, description="Уникальный идентификатор вопроса."),
     *                     @OA\Property(property="errors", type="array", description="Массив ошибок для данного вопроса.",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id_error", type="integer", example=1, description="Уникальный идентификатор ошибки (1 — ошибка в вопросе, 2 — ошибка в ответе)."),
     *                             @OA\Property(property="text_error", type="string", example="В вопросе викторины обнаружены лишние пробелы.", description="Описание ошибки.")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="minor_errors",
     *                 type="array",
     *                 description="Список незначительных ошибок. Каждая ошибка содержит информацию о вопросе и типе ошибки.",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id_question", type="integer", example=1, description="Уникальный идентификатор вопроса."),
     *                     @OA\Property(property="errors", type="array", description="Массив ошибок для данного вопроса.",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id_error", type="integer", example=1, description="Уникальный идентификатор ошибки (1 — незаполненная страница вопроса, 2 — незаполненный вариант ответа)."),
     *                             @OA\Property(property="text_error", type="string", example="Создана страница вопроса, но она не заполнена.", description="Описание ошибки.")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="logical_errors",
     *                 type="array",
     *                 description="Список логических ошибок. Каждая ошибка содержит информацию о вопросе и типе ошибки.",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id_question", type="integer", example=1, description="Уникальный идентификатор вопроса."),
     *                     @OA\Property(property="errors", type="array", description="Массив ошибок для данного вопроса.",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id_error", type="integer", example=1, description="Уникальный идентификатор ошибки (1 — дублирование вопроса, 2 — дублирование ответа)."),
     *                             @OA\Property(property="text_error", type="string", example="Встречаются одинаковые вопросы в викторине: №1, №2.", description="Описание ошибки.")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="critical_errors",
     *                 type="array",
     *                 description="Список критических ошибок. Каждая ошибка содержит информацию о вопросе и типе ошибки.",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id_question", type="integer", example=1, description="Уникальный идентификатор вопроса."),
     *                     @OA\Property(property="errors", type="array", description="Массив ошибок для данного вопроса.",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id_error", type="integer", example=1, description="Уникальный идентификатор ошибки (1 — null в тексте вопроса, 2 — недостаточное количество ответов, 3 — null в индексе правильного ответа, 4 — null в правильном ответе, 5 — пустой массив вопросов)."),
     *                             @OA\Property(property="text_error", type="string", example="Вопрос не должен быть null.", description="Описание ошибки.")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="name_quiz_errors",
     *                 type="array",
     *                 description="Список ошибок в названии викторины. Каждая ошибка содержит информацию о типе ошибки и её описании.",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id_error", type="integer", example=1, description="Уникальный идентификатор ошибки (1 — название слишком короткое, 2 — лишние пробелы)."),
     *                     @OA\Property(property="text_error", type="string", example="В названии викторины должно быть хотя бы 5 символов.", description="Описание ошибки.")
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Некорректные данные викторины."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="questions", type="array",
     *                     @OA\Items(type="string", example="The questions field is required.")
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
     * @param Request $request Запрос с данными викторины.
     * @return JsonResponse Ответ с перечнем ошибок викторины.
     */

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


    /**
     * Исправляет ошибки в викторине.
     *
     * Метод принимает данные викторины, включая вопросы и название, и исправляет ошибки в зависимости от переданных меток.
     * Если флаг `searchQuizErrors_flag` установлен в `true`, метод возвращает JSON-ответ с перечнем всех найденных ошибок.
     * В противном случае возвращаются только исправленные данные викторины.
     *
     * @OA\Post(
     *     path="/api/fix-quiz-errors",
     *     summary="Исправление ошибок в викторине",
     *     description="Метод исправляет ошибки в вопросах и названии викторины в зависимости от переданных меток. Возвращает либо исправленные данные, либо список ошибок.",
     *     tags={"CreatorQuiz"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные викторины для исправления ошибок",
     *         @OA\JsonContent(
     *             required={"questions", "errors"},
     *             @OA\Property(
     *                 property="questions",
     *                 type="array",
     *                 description="Массив вопросов викторины",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1, description="Уникальный идентификатор вопроса."),
     *                     @OA\Property(property="question", type="string", example="What is your name?", description="Текст вопроса. Может быть пустым или содержать `null`."),
     *                     @OA\Property(property="answers", type="array", description="Массив вариантов ответов. Может содержать пустые строки или `null`.",
     *                         @OA\Items(type="string", example="John")
     *                     ),
     *                     @OA\Property(property="correctAnswerIndex", type="integer", example=0, description="Индекс правильного ответа в массиве answers."),
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="quizName",
     *                 type="string",
     *                 example="Тест по программированию",
     *                 description="Название викторины. Если отсутствует, используется пустая строка."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Метки на исправление ошибок",
     *                 @OA\Property(property="cosmeticErrors", type="boolean", example=true, description="Метка для исправления косметических ошибок."),
     *                 @OA\Property(property="minorErrors", type="boolean", example=true, description="Метка для исправления несущественных ошибок."),
     *                 @OA\Property(property="logicalErrors", type="boolean", example=true, description="Метка для исправления логических ошибок."),
     *                 @OA\Property(property="cosmeticErrorQuizName", type="boolean", example=true, description="Метка для исправления косметических ошибок в названии викторины.")
     *             ),
     *             @OA\Property(
     *                 property="searchQuizErrors_flag",
     *                 type="boolean",
     *                 example=true,
     *                 description="Метка для возвращения списка ошибок. Если `true`, метод возвращает список ошибок."
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное исправление ошибок",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(
     *                         property="questions",
     *                         type="array",
     *                         description="Исправленные вопросы викторины",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1, description="Уникальный идентификатор вопроса."),
     *                             @OA\Property(property="question", type="string", example="What is your name?", description="Текст вопроса."),
     *                             @OA\Property(property="answers", type="array", description="Массив вариантов ответов.",
     *                                 @OA\Items(type="string", example="John")
     *                             ),
     *                             @OA\Property(property="correctAnswerIndex", type="integer", example=0, description="Индекс правильного ответа в массиве answers."),
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="quizName",
     *                         type="string",
     *                         example="Тест по программированию",
     *                         description="Исправленное название викторины."
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(
     *                         property="questions",
     *                         type="array",
     *                         description="Исправленные вопросы викторины",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1, description="Уникальный идентификатор вопроса."),
     *                             @OA\Property(property="question", type="string", example="What is your name?", description="Текст вопроса."),
     *                             @OA\Property(property="answers", type="array", description="Массив вариантов ответов.",
     *                                 @OA\Items(type="string", example="John")
     *                             ),
     *                             @OA\Property(property="correctAnswerIndex", type="integer", example=0, description="Индекс правильного ответа в массиве answers."),
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="quizName",
     *                         type="string",
     *                         example="Тест по программированию",
     *                         description="Исправленное название викторины."
     *                     ),
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Список ошибок викторины",
     *                         @OA\Property(
     *                             property="cosmetic_errors",
     *                             type="array",
     *                             description="Список косметических ошибок",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id_question", type="integer", example=1, description="Уникальный идентификатор вопроса."),
     *                                 @OA\Property(property="errors", type="array", description="Массив ошибок для данного вопроса.",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="id_error", type="integer", example=1, description="Уникальный идентификатор ошибки (1 — ошибка в вопросе, 2 — ошибка в ответе)."),
     *                                         @OA\Property(property="text_error", type="string", example="В вопросе викторины обнаружены лишние пробелы.", description="Описание ошибки.")
     *                                     )
     *                                 )
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="minor_errors",
     *                             type="array",
     *                             description="Список незначительных ошибок",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id_question", type="integer", example=1, description="Уникальный идентификатор вопроса."),
     *                                 @OA\Property(property="errors", type="array", description="Массив ошибок для данного вопроса.",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="id_error", type="integer", example=1, description="Уникальный идентификатор ошибки (1 — незаполненная страница вопроса, 2 — незаполненный вариант ответа)."),
     *                                         @OA\Property(property="text_error", type="string", example="Создана страница вопроса, но она не заполнена.", description="Описание ошибки.")
     *                                     )
     *                                 )
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="logical_errors",
     *                             type="array",
     *                             description="Список логических ошибок",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id_question", type="integer", example=1, description="У уникальный идентификатор вопроса."),
     *                                 @OA\Property(property="errors", type="array", description="Массив ошибок для данного вопроса.",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="id_error", type="integer", example=1, description="Уникальный идентификатор ошибки (1 — дублирование вопроса, 2 — дублирование ответа)."),
     *                                         @OA\Property(property="text_error", type="string", example="Встречаются одинаковые вопросы в викторине: №1, №2.", description="Описание ошибки.")
     *                                     )
     *                                 )
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="critical_errors",
     *                             type="array",
     *                             description="Список критических ошибок",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id_question", type="integer", example=1, description="Уникальный идентификатор вопроса."),
     *                                 @OA\Property(property="errors", type="array", description="Массив ошибок для данного вопроса.",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="id_error", type="integer", example=1, description="Уникальный идентификатор ошибки (1 — null в тексте вопроса, 2 — недостаточное количество ответов, 3 — null в индексе правильного ответа, 4 — null в правильном ответе, 5 — пустой массив вопросов)."),
     *                                         @OA\Property(property="text_error", type="string", example="Вопрос не должен быть null.", description="Описание ошибки.")
     *                                     )
     *                                 )
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="name_quiz_errors",
     *                             type="array",
     *                             description="Список ошибок в названии викторины",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id_error", type="integer", example=1, description="Уникальный идентификатор ошибки (1 — название слишком короткое, 2 — лишние пробелы)."),
     *                                 @OA\Property(property="text_error", type="string", example="В названии викторины должно быть хотя бы 5 символов.", description="Описание ошибки.")
     *                             )
     *                         )
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Некорректные данные викторины."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="questions", type="array",
     *                     @OA\Items(type="string", example="The questions field is required.")
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
     * @param Request $request Запрос с данными викторины.
     * @return JsonResponse Ответ с исправленными данными или списком ошибок.
     */

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


    /**
     * Создает викторину и сохраняет её в базу данных.
     *
     * Метод принимает данные викторины, включая вопросы, название и метки на исправление ошибок.
     * Если ошибки присутствуют, они исправляются перед сохранением викторины в базу данных.
     * После успешного создания викторины происходит оповещение сервера User через кафку.
     *
     * @OA\Post(
     *     path="/api/create-quiz",
     *     summary="Создание викторины",
     *     description="Метод создает викторину, исправляет ошибки (если они есть) и сохраняет её в базу данных. После успешного создания данные отправляются на внешний сервер.",
     *     tags={"CreatorQuiz"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные викторины для создания",
     *         @OA\JsonContent(
     *             required={"questions", "errors", "id_user"},
     *             @OA\Property(
     *                 property="questions",
     *                 type="array",
     *                 description="Массив вопросов викторины",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1, description="Уникальный идентификатор вопроса."),
     *                     @OA\Property(property="question", type="string", example="What is your name?", description="Текст вопроса. Может быть пустым или содержать `null`."),
     *                     @OA\Property(
     *                         property="answers",
     *                         type="array",
     *                         description="Массив вариантов ответов. Может содержать пустые строки или `null`.",
     *                         @OA\Items(
     *                             type="string",
     *                             example="John",
     *                             description="Текст ответа."
     *                         ),
     *                         example={"John", "Tom"}
     *                     ),
     *                     @OA\Property(property="correctAnswerIndex", type="integer", example=0, description="Индекс правильного ответа в массиве answers.")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="quizName",
     *                 type="string",
     *                 example="Тест по программированию",
     *                 description="Название викторины. Если отсутствует, используется пустая строка."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Метки на исправление ошибок",
     *                 @OA\Property(property="cosmeticErrors", type="boolean", example=true, description="Метка для исправления косметических ошибок."),
     *                 @OA\Property(property="minorErrors", type="boolean", example=true, description="Метка для исправления несущественных ошибок."),
     *                 @OA\Property(property="logicalErrors", type="boolean", example=true, description="Метка для исправления логических ошибок."),
     *                 @OA\Property(property="cosmeticErrorQuizName", type="boolean", example=true, description="Метка для исправления косметических ошибок в названии викторины.")
     *             ),
     *             @OA\Property(
     *                 property="login",
     *                 type="string",
     *                 format="string",
     *                 example="k12345a",
     *                 description="Логин пользователя, создающего викторину."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Успешное создание викторины",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success", description="Статус операции."),
     *             @OA\Property(property="operation_index", type="integer", example=1, description="Индекс операции.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Некорректные данные викторины."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="questions", type="array",
     *                     @OA\Items(type="string", example="The questions field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error", description="Статус операции."),
     *             @OA\Property(property="message", type="string", example="Произошла ошибка при создании викторины.", description="Сообщение об ошибке."),
     *             @OA\Property(property="error", type="string", example="Сообщение об ошибке", description="Детали ошибки.")
     *         )
     *     )
     * )
     *
     * @param CreateQuizRequest $request Запрос с данными викторины.
     * @return JsonResponse Ответ с результатом создания викторины.
     */

    public function createQuiz(CreateQuizRequest $request): \Illuminate\Http\JsonResponse
    {
        // Получаем данные из запроса
        $data = $request->all();
        $questions = $data['questions']; // Массив вопросов
        $quizName = $data['quizName'] ?? ''; // Название викторины; если quizName отсутствует или null, используем пустую строку
        $errors = $data['errors']; // Массив меток на исправление ошибок
        $login = $data['login']; // Логин пользователя

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
            CreatorQuizHelper::saveQuizToDatabase($quizName, $questions, $login);

//            // Отправка данных викторины на внешний сервер в фоновом режиме
//            Queue::push(new NotifyUserServerAboutUserQuiz([
//                'login' => $login_user
//            ]));


            // Использование сервиса Kafka
            // Отправка в Kafka в отдельном try-catch
            try {
                KafkaService::publish(
                    'localhost',
                    'quiz_created',
                    ['login' => $login]
                );
            } catch (Exception $kafkaException) {
                Log::error('Ошибка Kafka: ' . $kafkaException->getMessage());
                // Не прерываем выполнение, только логируем
            }

            // Возвращаем успешный ответ
            return response()->json(['status' => 'success', 'operation_index' => 1], 201);
        } catch (Exception $e) {
            Log::error('Ошибка при создании викторины: ' . $e->getMessage());

            // Возвращаем ошибку
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при создании викторины.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
