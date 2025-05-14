<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuizRequest;
use App\Services\CreatorQuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreatorQuizController extends Controller
{
    private CreatorQuizService $creatorQuizService;

    /**
     * Конструктор контроллера для создания экземпляра Сервиса
     *
     * @param CreatorQuizService $creatorQuizService
     */
    public function __construct(CreatorQuizService $creatorQuizService)
    {
        $this->creatorQuizService = $creatorQuizService;
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
    public function searchQuizErrors(Request $request): JsonResponse
    {
        return $this->creatorQuizService->searchQuizErrors($request);
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
    public function fixQuizErrors(Request $request): JsonResponse
    {
        return $this->creatorQuizService->fixQuizErrors($request);
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
     * @param QuizRequest $request Запрос с данными викторины.
     * @return JsonResponse Ответ с результатом создания викторины.
     */
    public function createQuiz(QuizRequest $request): JsonResponse
    {
        return $this->creatorQuizService->createQuiz($request);
    }
}
