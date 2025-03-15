<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreatorQuizController extends Controller
{
    public function searchQuizErrors(Request $request): \Illuminate\Http\JsonResponse
    {
        // Получаем данные из запроса
        $data = $request->all();
        $questions = $data['questions']; // Массив вопросов
        $quizName = $data['quizName'] ?? ''; //// Название викторины; если quizName отсутствует или null, используем пустую строку

        Log::info("Полученные данные в метод searchQuizErrors:", $data); // Логирование данных

        $questions = $this->cleanEmptyFields($questions); //Обращение фактически пустых строк в null
        $cosmetic_errors = $this->checkDataForCosmeticErrors($questions); //Формирование списка косметических ошибок
        $questions = $this->trimQuestions($questions); //'Trim' полей вопросов
        $minor_errors = $this->checkDataForMinorErrors($questions); //Формирование списка несущественных ошибок

        //Log::info("questions 1:", $questions); // Логирование данных

        $questions = $this->filterQuestions($questions); // Фильтрация страничек вопросов

        //Log::info("questions 2:", $questions); // Логирование данных

        $questions = $this->filterAnswers($questions); // Фильтрация вариантов ответов вопросов

        //Log::info("questions 3:", $questions); // Логирование данных

        $logical_errors = $this->checkDataForLogicalErrors($questions); //Формирование списка критических ошибок

        //Log::info("questions 4:", $questions); // Логирование данных

        $critical_errors = $this->checkDataForCriticalErrors($questions); //Формирование списка критических ошибок

        //Log::info("logical_errors:", $logical_errors); // Логирование данных

        $name_quiz_errors = $this->checkNameQuizForErrors($quizName); //Формирование списка ошибок названия викторины


        // Возвращаем ответ клиенту
        return response()->json([
            'cosmetic_errors' => $cosmetic_errors,
            'minor_errors' => $minor_errors,
            'critical_errors' => $critical_errors,
            'logical_errors' => $logical_errors,
            'name_quiz_errors' => $name_quiz_errors
        ], 200);
    }

    public function fixQuizErrors(Request $request): \Illuminate\Http\JsonResponse
    {
        // Получаем данные из запроса
        $data = $request->all();
        $questions = $data['questions']; // Массив вопросов
        $quizName = $data['quizName'];   // Название викторины

        // Флаги для различных типов ошибок
        $cosmetic_errors_flag = $data['cosmetic_errors_flag'];
        $minor_errors_flag = $data['minor_errors_flag'];
        $logical_errors_flag = $data['logical_errors_flag'];
        $quiz_name_cosmetic_errors_flag = $data['quiz_name_cosmetic_errors_flag'];
        $create_quiz_flag = $data['create_quiz_flag'];

        // Логирование данных
        Log::info("Полученные данные в метод fixQuizErrors:", $data);

        // Очистка пустых полей
        $questions = $this->cleanEmptyFields($questions);

        // Обработка ошибок в вопросах
        if ($cosmetic_errors_flag) {
            $questions = $this->trimQuestions($questions);
        }
        if ($minor_errors_flag) {
            $questions = $this->filterQuestions($questions);
            $questions = $this->filterAnswers($questions);
        }
        if ($logical_errors_flag) {
            $questions = $this->fixLogicalErrors($questions);
        }

        // Обработка ошибок в названии викторины
        if ($quiz_name_cosmetic_errors_flag) {
            $quizName = trim($quizName);
        }

        // Проверка на создание викторины
        if (!$create_quiz_flag) {
            // Вызов метода для поиска ошибок
            $requestForErrors = new Request([
                'questions' => $questions,
                'quizName' => $quizName,
            ]);

            $errorResponse = $this->searchQuizErrors($requestForErrors);
            $errorsData = json_decode($errorResponse->getContent(), true);

            // Возвращаем ответ клиенту
            return response()->json([
                'questions' => $questions,
                'quizName' => $quizName,
                'cosmetic_errors' => $errorsData['cosmetic_errors'],
                'minor_errors' => $errorsData['minor_errors'],
                'critical_errors' => $errorsData['critical_errors'],
                'logical_errors' => $errorsData['logical_errors'],
                'name_quiz_errors' => $errorsData['name_quiz_errors'],
            ], 200);
        } else {
            // Вызов метода для создания викторины
            $requestForBD = new Request([
                'questions' => $questions,
                'quizName' => $quizName,
            ]);

            $responseBD = $this->createQuizWithQuestions($requestForBD);
            $responseBDMessage = json_decode($responseBD->getContent(), true);

            // Возвращаем ответ клиенту
            return response()->json([
                'message' => $responseBDMessage['message']
            ], 200);
        }
    }


    public function createQuizWithQuestions(Request $request): \Illuminate\Http\JsonResponse
    {
        // Получаем данные из запроса
        $data = $request->all();
        $questions = $data['questions']; // Массив вопросов
        $quizName = $data['quizName'];   // Название викторины

        // Валидация входящих данных
        $request->validate([
            'quizName' => 'required',
            'questions' => 'required|array',
            'questions.*.question' => 'required',
            'questions.*.answers' => 'required|array',
            'questions.*.correctAnswerIndex' => 'required|integer',
        ]);

        // Начало транзакции
        DB::transaction(function () use ($quizName, $questions) {
            // Создание викторины
            $quiz = Quiz::create([
                'name_quiz' => $quizName,
                'is_ready' => true,
                'id_user' => null,
            ]);

            // Создание вопросов викторины
            foreach ($questions as $question) {
                // Получаем текст вопроса
                $textQuestion = $question['question'];

                // Получаем правильный ответ
                $correctOption = $question['answers'][$question['correctAnswerIndex']];

                // Получаем неправильные ответы (все остальные)
                $wrongOptions = array_filter($question['answers'], function($answer) use ($correctOption) {
                    return $answer !== $correctOption;
                });

                // Создание записи вопроса
                QuizQuestion::create([
                    'text_question' => $textQuestion,
                    'correct_option' => $correctOption,
                    'wrong_option' => json_encode(array_values($wrongOptions)), // Преобразуем в JSON
                    'id_quiz' => $quiz->id_quiz,
                ]);
            }
        });

        return response()->json(['message' => 'Викторина и вопросы успешно созданы!'], 201);
    }


    /**
     * Проверяет и очищает поля, содержащие только пробелы, символы новой строки и табуляции.
     *
     * @param array $questions Массив вопросов и ответов.
     * @return array Массив с очищенными полями.
     */
    private function cleanEmptyFields(array $questions): array
    {
        foreach ($questions as &$question) {
            // Проверка текста вопроса
            if (isset($question['text']) && empty(trim($question['text'], " \n\t"))) {
                $question['text'] = null;
            }

            // Проверка полей вариантов ответа
            if (isset($question['answers']) && is_array($question['answers'])) {
                foreach ($question['answers'] as &$answer) {
                    if (empty(trim($answer, " \n\t"))) {
                        $answer = null;
                    }
                }
            }
        }

        return $questions;
    }


    /**
     * Проверяет текст вопросов и ответов викторины на наличие лишних пробелов.
     *
     * Функция анализирует переданный массив вопросов и возвращает список ошибок,
     * если в тексте вопроса или ответа обнаружены два или более пробелов подряд.
     *
     * @param array $questions Массив вопросов викторины. Каждый вопрос должен содержать:
     *                         - 'id' (int): Уникальный идентификатор вопроса.
     *                         - 'text' (string): Текст вопроса.
     *                         - 'answers' (array): Массив текстов вариантов ответа.
     *                         - 'correctAnswerIndex' (int): Индекс правильного ответа (не используется).
     *
     * @return array Массив с результатами проверки. Каждый элемент массива содержит:
     *              - 'id_question' (int): Идентификатор вопроса.
     *              - 'errors' (array): Список ошибок, если они обнаружены. Каждая ошибка содержит:
     *                - 'id_error' (int): Код ошибки (1 — для вопроса, 2 — для ответа).
     *                - 'text_error' (string): Текстовое описание ошибки.
     *
     */
    private function checkDataForCosmeticErrors(array $questions): array
    {
        $result = [];

        foreach ($questions as $question) {
            $errors = [];

            // Проверка текста вопроса на лишние пробелы
            if (isset($question['text']) && preg_match('/\s{2,}/', $question['text'])) {
                $errors[] = [
                    "id_error" => 1,
                    "text_error" => "В вопросе викторины обнаружены лишние пробелы."
                ];
            }

            // Проверка полей вариантов ответа на лишние пробелы
            if (isset($question['answers']) && is_array($question['answers'])) {
                foreach ($question['answers'] as $index => $answer) {
                    if (isset($answer) && preg_match('/\s{2,}/', $answer)) {
                        $errors[] = [
                            "id_error" => 2,
                            "text_error" => "В поле варианта ответа №" . ($index + 1) . " обнаружены лишние пробелы."
                        ];
                    }
                }
            }

            // Добавляем ошибки в результат, если они есть
            if (!empty($errors)) {
                $result[] = [
                    "id_question" => $question['id'],
                    "errors" => $errors
                ];
            }
        }

        return $result;
    }


    /**
     * Очищает массив вопросов, удаляя лишние пробелы, табуляции и знаки переноса
     *
     * Для каждого вопроса:
     * - Применяет trim к полю question
     * - Применяет trim ко всем элементам массива answers
     * - Устанавливает значение поля question в null, если оно пустое после очистки
     * - Устанавливает элементы массива answers в null, если они пустые после очистки
     *
     * @param array $questions Массив вопросов, где каждый вопрос содержит 'question' и 'answers'
     * @return array Очищенный массив вопросов
     */
    private function trimQuestions(array $questions): array
    {
        return array_map(function($question) {
            // Применяем trim к полю question
            $question['question'] = trim($question['question']);

            // Применяем trim ко всем элементам массива answers
            $question['answers'] = array_map(function($answer) {
                return trim($answer);
            }, $question['answers']);

            // Проверяем поле question и массив answers на пустые значения
            $question['question'] = $question['question'] === '' ? null : $question['question'];
            $question['answers'] = array_map(function($answer) {
                return $answer === '' ? null : $answer;
            }, $question['answers']);

            return $question;
        }, $questions);
    }

    /**
     * Проверяет массив вопросов на наличие незначительных ошибок.
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен ассоциативным массивом
     *                         с ключами:
     *                         - 'id' (int): Идентификатор вопроса.
     *                         - 'question' (string|null): Текст вопроса.
     *                         - 'answers' (array): Массив ответов, где каждый элемент может быть строкой или null.
     *                         - 'correctAnswerIndex' (int|null): Индекс правильного ответа.
     *
     * @return array Массив с результатами проверки, где каждый элемент содержит:
     *               - 'id_question' (int): Идентификатор вопроса.
     *               - 'errors' (array): Массив ошибок, где каждая ошибка представлена ассоциативным массивом
     *                 с ключами:
     *                 - 'id_error' (int): Идентификатор ошибки.
     *                 - 'text_error' (string): Описание ошибки.
     *
     */

    private function checkDataForMinorErrors(array $questions): array
    {
        $result = [];

        foreach ($questions as $question) {
            $errors = [];

            // Проверка на незаполненную страницу вопроса
            if (is_null($question['question']) && (empty($question['answers']) || array_all($question['answers'], fn($answer) => is_null($answer)))) {
                $errors[] = ["id_error" => 1, "text_error" => "Создана страница вопроса, но она не заполнена."];
            } else {
                // Проверка на пустые поля ответов
                foreach ($question['answers'] as $index => $answer) {
                    if (is_null($answer) && ($question['correctAnswerIndex'] != $index)) {
                        $errors[] = ["id_error" => 2, "text_error" => "Создано поле варианта ответа №" . ($index + 1) . ", но оно не заполнено."];
                    }
                }
            }

            // Добавляем ошибки в результат, если они есть
            if (!empty($errors)) {
                $result[] = [
                    "id_question" => $question['id'],
                    "errors" => $errors
                ];
            }
        }

        return $result;
    }


    /**
     * Фильтрует массив вопросов, удаляя те, у которых отсутствует текст вопроса
     * и нет ненулевых ответов
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен
     * ассоциативным массивом с ключами 'question' и 'answers'
     *
     * @return array Отфильтрованный массив вопросов, содержащий только те
     * вопросы, которые имеют ненулевой текст вопроса или хотя бы
     * один ненулевой ответ
     */
    private function filterQuestions(array $questions): array
    {
        return array_filter($questions, function($question) {
            return !(
                is_null($question['question']) && (empty($question['answers'])
                    || array_filter($question['answers'], function($answer) {
                        return !is_null($answer);
                    }) === [])
            );
        });
    }

    /**
     * Удаляет элементы из массива ответов каждого вопроса, которые равны null,
     * кроме элемента с индексом correctAnswerIndex (если сам correctAnswerIndex не равен null)
     *
     * @param array $questions Массив вопросов, где каждый вопрос содержит 'answers' и 'correctAnswerIndex'
     * @return array Массив отфильтрованных вопросов с обновленными индексами правильного ответа
     */
    private function filterAnswers(array $questions): array
    {
        return array_map(function($question) {
            $answers = $question['answers'];
            $correctAnswerIndex = $question['correctAnswerIndex'];

            // Проверяем, есть ли правильный ответ
            $correctAnswer = !is_null($correctAnswerIndex) ? $answers[$correctAnswerIndex] : null;

            // Если правильного ответа нет, возвращаем отфильтрованный массив без него
            if (is_null($correctAnswerIndex)) {
                return [
                    'id' => $question['id'],
                    'question' => $question['question'],
                    'answers' => array_values(array_filter($answers, fn($answer) => !is_null($answer))),
                    'correctAnswerIndex' => null,
                ];
            }

            // Фильтруем массив, оставляя все ненулевые значения, кроме правильного ответа
            $filteredAnswers = array_filter($answers, function($answer, $index) use ($correctAnswerIndex) {
                return !is_null($answer) || $index === $correctAnswerIndex;
            }, ARRAY_FILTER_USE_BOTH);

            // Изменяем индексацию под новый массив
            $filteredAnswers = array_values($filteredAnswers);

            // Пересчитываем новый индекс правильного ответа в отфильтрованном массиве
            $newCorrectAnswerIndex = array_search($correctAnswer, $filteredAnswers, true);

            // Возвращаем вопрос с отфильтрованными ответами и обновленным индексом
            return [
                'id' => $question['id'],
                'question' => $question['question'],
                'answers' => array_values($filteredAnswers), // Приводим массив к индексам 0, 1, 2...
                'correctAnswerIndex' => $newCorrectAnswerIndex,
            ];
        }, $questions);
    }

    /**
     * Находит логические ошибки в массиве вопросов и ответов.
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен ассоциативным массивом
     *                         с ключами:
     *                         - 'id' (int): Идентификатор вопроса.
     *                         - 'question' (string): Текст вопроса.
     *                         - 'answers' (array): Массив ответов, где каждый элемент имеет ключ 'id' (int)
     *                           и 'text' (string).
     *
     * @return array Массив с описанием ошибок, где каждая ошибка представлена строкой.
     */
    public function checkDataForLogicalErrors(array $questions): array
    {
        $errors = [];

        // Проверка на дублирование вопросов
        $questionTexts = [];
        foreach ($questions as $question) {
            $questionText = $question['question'];
            if (isset($questionTexts[$questionText])) {
                $questionTexts[$questionText][] = $question['id'];
            } else {
                $questionTexts[$questionText] = [$question['id']];
            }
        }

        // Формирование ошибок для дублированных вопросов
        foreach ($questionTexts as $text => $ids) {
            if (count($ids) > 1) {
                foreach ($ids as $id) {
                    $errors[$id][] = [
                        "id_error" => 1,
                        "text_error" => "Встречаются одинаковые вопросы в викторине: №" . implode(", №", $ids). "."
                    ];
                }
            }
        }

        // Проверка на дублирование вариантов ответов
        foreach ($questions as $question) {
            $answerTexts = [];
            foreach ($question['answers'] as $index => $answerText) {
                if (isset($answerTexts[$answerText])) {
                    $answerTexts[$answerText][] = $index + 1; // Нумерация с 1
                } else {
                    $answerTexts[$answerText] = [$index + 1];
                }
            }

            // Формирование ошибок для дублированных ответов
            foreach ($answerTexts as $text => $indices) {
                if (count($indices) > 1) {
                    $errors[$question['id']][] = [
                        "id_error" => 2,
                        "text_error" => "Встречаются одинаковые варианты ответов в полях: №" . implode(", №", $indices). "."
                    ];
                }
            }
        }

        // Преобразование массива в ожидаемый формат
        $formattedErrors = [];
        foreach ($errors as $id_question => $errorList) {
            $formattedErrors[] = [
                "id_question" => $id_question,
                "errors" => $errorList
            ];
        }

        return $formattedErrors;
    }

    /**
     * Исправляет логические ошибки в массиве вопросов и ответов.
     *
     * @param array $questions Массив вопросов.
     *
     * @return array Исправленный массив вопросов.
     */
    private function fixLogicalErrors(array $questions): array
    {
        $uniqueQuestions = [];
        $questionTexts = [];

        // Удаление дублирующихся вопросов
        foreach ($questions as $question) {
            $questionText = $question['question'];

            if (!in_array($questionText, $questionTexts)) {
                $questionTexts[] = $questionText;
                $uniqueQuestions[] = $question; // Сохраняем уникальный вопрос
            }
        }

        // Удаление дублирующихся ответов и пересчет индексов
        foreach ($uniqueQuestions as &$question) {
            $answerTexts = [];
            $uniqueAnswers = [];
            $correctAnswerIndex = $question['correctAnswerIndex'];

            foreach ($question['answers'] as $index => $answerText) {
                if (!in_array($answerText, $answerTexts)) {
                    $answerTexts[] = $answerText;
                    $uniqueAnswers[] = $answerText;
                }
            }

            // Обновляем массив ответов
            $question['answers'] = $uniqueAnswers;

            // Проверяем правильный индекс
            if ($correctAnswerIndex !== null) { // Проверяем, что индекс не равен null
                $correctAnswer = $question['answers'][$correctAnswerIndex] ?? null; // Получаем правильный ответ

                // Находим новый индекс правильного ответа
                $newIndex = array_search($correctAnswer, $uniqueAnswers);

                // Если правильный ответ найден, обновляем индекс
                if ($newIndex !== false) {
                    $question['correctAnswerIndex'] = $newIndex;
                } else {
                    $question['correctAnswerIndex'] = 0; // Если не найден, устанавливаем на 0
                }
            }
        }

        // Пересчет индексов вопросов
        foreach ($uniqueQuestions as $key => &$question) {
            $question['id'] = $key + 1; // Обновляем id, начиная с 1
        }

        return $uniqueQuestions;
    }




    /**
     * Проверяет массив вопросов на наличие критических ошибок.
     *
     * @param array $questions Массив вопросов.
     * @return array Возвращает массив с ошибками для каждого вопроса.
     */
    private function checkDataForCriticalErrors(array $questions): array
    {
        $result = [];

        foreach ($questions as $question) {
            $errors = [];

            // Проверка на null для question
            if (is_null($question['question'])) {
                $errors[] = ["id_error" => 1, "text_error" => "Вопрос не должен быть null"];
            }

            // Проверка на количество ответов
            if (count($question['answers']) < 2) {
                $errors[] = ["id_error" => 2, "text_error" => "Должно быть как минимум 2 ответа"];
            }

            // Проверка на null для correctAnswerIndex
            if (is_null($question['correctAnswerIndex'])) {
                $errors[] = ["id_error" => 3, "text_error" => "Индекс правильного ответа не может быть null"];
            }

            if (!is_null($question['correctAnswerIndex'])) {
                if (is_null($question['answers'][$question['correctAnswerIndex']])) {
                    $errors[] = ["id_error" => 4, "text_error" => "Правильный ответ не может быть null"];
                }
            }

            // Добавляем ошибки в результат, если они есть
            if (!empty($errors)) {
                $result[] = [
                    "id_question" => $question['id'],
                    "errors" => $errors
                ];
            }
        }

        return $result;
    }


    /**
     * Проверяет название викторины на наличие ошибок.
     *
     * Функция анализирует переданное название викторины и возвращает массив ошибок,
     * если они обнаружены. Проверяются как критические ошибки (например, недостаточная длина названия),
     * так и косметические (например, лишние пробелы).
     *
     * @param string $quizName Название викторины для проверки.
     * @return array Ассоциативный массив ошибок. Возможные ключи:
     *               - 'critical_error': Критическая ошибка (например, длина названия менее 5 символов).
     *               - 'cosmetic_error': Косметическая ошибка (например, лишние пробелы в названии).
     */
    private function checkNameQuizForErrors(string $quizName): array
    {
        $errors = [];

        // Очистка строки от лишних пробелов, табуляций и переносов строк
        $cleanedQuizName = trim($quizName, " \t\n\r\0\x0B");

        // Удаление лишних пробелов внутри строки
        $cleanedQuizName = preg_replace('/\s+/', ' ', $cleanedQuizName);

        // Критические ошибки
        if (mb_strlen($cleanedQuizName) < 5) {
            $errors['critical_error'] = "В названии викторины должно быть хотя бы 5 символов.";
        }

        // Косметические ошибки (проверка на лишние пробелы)
        if (preg_match('/\s{2,}/', $quizName)) {
            $errors['cosmetic_error'] = "В названии викторины обнаружены лишние пробелы.";
        }

        return $errors;
    }



    // Публичный метод для заменя фактически пустых полей на null
    public function getCleanEmptyFields(array $questions): array
    {
        return $this->cleanEmptyFields($questions);
    }

    // Публичный метод для выведения списка косметических ошибок
    public function getCheckDataForCosmeticErrors(array $questions): array
    {
        return $this->checkDataForCosmeticErrors($questions);
    }

    // Публичный метод для доступа к 'триму' вопросов
    public function getTrimQuestions(array $questions): array
    {
        return $this->trimQuestions($questions);
    }

    // Тест на поиск незначительных ошибок
    public function getCheckDataForMinorErrors(array $questions): array
    {
        return $this->checkDataForMinorErrors($questions);
    }

    // Публичный метод для доступа к фильтрации вопросов
    public function getFilteredQuestions(array $questions): array
    {
        return $this->filterQuestions($questions);
    }

    // Публичный метод для доступа к фильтрации ответов вопросов
    public function getQuestionsWithFilteredAnswers(array $questions): array
    {
        return $this->filterAnswers($questions);
    }

    // Тест на поиск критических ошибок
    public function getCheckDataForCriticalErrors(array $questions): array
    {
        return $this->checkDataForCriticalErrors($questions);
    }

    // Тест на поиск логических ошибок
    public function getCheckDataForLogicalErrors(array $questions): array
    {
        return $this->checkDataForLogicalErrors($questions);
    }

    // Тест на исправление логических ошибок
    public function getFixLogicalErrors(array $questions): array
    {
        return $this->fixLogicalErrors($questions);
    }

    // Тест на поиск ошибок названия викторины
    public function getCheckNameQuizForErrors(string $quizName): array
    {
        return $this->checkNameQuizForErrors($quizName);
    }

}
