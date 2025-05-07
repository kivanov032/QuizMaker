<?php

namespace App\Helpers;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class CreatorQuizHelper
{

    /**
     * Очищает пустые поля в массиве вопросов.
     *
     * Эта функция принимает массив вопросов и очищает пустые поля в каждом вопросе.
     * Она проверяет текст вопроса и каждый вариант ответа на пустоту, и если поле пустое,
     * то оно заменяется на значение `null`.
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен в виде ассоциативного массива с ключами:
     *                         - `question`: строка с текстом вопроса
     *                         - `answers`: массив строк с вариантами ответов
     *                         - `correctAnswerIndex`: целое число, индекс правильного ответа в массиве `answers`
     * @return array Возвращает массив вопросов с очищенными пустыми полями.
     */
    public static function cleanEmptyFields(array $questions): array
    {
        foreach ($questions as &$question) {
            // Проверка текста вопроса
            if (isset($question['question']) && empty(trim($question['question'], " \n\t"))) {
                $question['question'] = null;
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
     * Проверяет массив вопросов на наличие косметических ошибок.
     *
     * Эта функция принимает массив вопросов и проверяет каждый вопрос на наличие лишних пробелов в тексте вопроса
     * и вариантах ответов. Если такие ошибки обнаружены, то для каждого вопроса с ошибками формируется массив с
     * информацией об ошибках. Результирующий массив содержит информацию обо всех вопросах с ошибками.
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен в виде ассоциативного массива с ключами:
     *                         - `id`: уникальный идентификатор вопроса
     *                         - `question`: строка с текстом вопроса
     *                         - `answers`: массив строк с вариантами ответов
     *                         - `correctAnswerIndex`: целое число, индекс правильного ответа в массиве `answers`
     * @return array Возвращает массив с информацией о вопросах, в которых были обнаружены косметические ошибки.
     *               Каждый элемент результирующего массива - ассоциативный массив с ключами:
     *               - `id_question`: уникальный идентификатор вопроса
     *               - `errors`: массив ошибок, где каждая ошибка представлена в виде ассоциативного массива с ключами:
     *                 - `id_error`: уникальный идентификатор ошибки
     *                 - `text_error`: текстовое описание ошибки
     */
    public static function checkDataForCosmeticErrors(array $questions): array
    {
        $result = [];

        foreach ($questions as $question) {
            $errors = [];

            // Проверка текста вопроса на лишние пробелы
            if (isset($question['question']) && preg_match('/\s{2,}/', $question['question'])) {
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
     * Обрезает лишние пробелы в тексте вопросов и вариантах ответов.
     *
     * Эта функция принимает массив вопросов и применяет следующие действия:
     * 1. Для поля `question` каждого вопроса:
     *    - Удаляет пробелы в начале и конце строки с помощью `trim()`.
     *    - Заменяет последовательности пробелов внутри строки на одиночные пробелы с помощью `preg_replace()`.
     *    - Если в результате строка становится пустой, заменяет ее на `null`.
     * 2. Для поля `answers` каждого вопроса:
     *    - Для каждого варианта ответа применяет те же действия, что и для поля `question`.
     *    - Если в результате строка становится пустой, заменяет ее на `null`.
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен в виде ассоциативного массива с ключами:
     *                         - `question`: строка с текстом вопроса
     *                         - `answers`: массив строк с вариантами ответов
     *                         - `correctAnswerIndex`: целое число, индекс правильного ответа в массиве `answers`
     * @return array Возвращает массив вопросов с обрезанными лишними пробелами.
     */
    public static function trimQuestions(array $questions): array
    {
        return array_map(function ($question) {
            // Применяем trim и заменяем лишние пробелы внутри строки для поля question
            if (isset($question['question'])) {
                $question['question'] = trim($question['question']);
                $question['question'] = preg_replace('/\s+/', ' ', $question['question']);
                $question['question'] = $question['question'] === '' ? null : $question['question'];
            }

            // Применяем trim и заменяем лишние пробелы внутри строки для всех элементов массива answers
            if (isset($question['answers']) && is_array($question['answers'])) {
                $question['answers'] = array_map(function ($answer) {
                    if (isset($answer)) {
                        $answer = trim($answer);
                        $answer = preg_replace('/\s+/', ' ', $answer);
                        return $answer === '' ? null : $answer;
                    }
                    return null;
                }, $question['answers']);
            }

            return $question;
        }, $questions);
    }


    /**
     * Обрезает лишние пробелы в строке и возвращает обрезанную строку или null.
     *
     * Эта функция принимает строку в качестве входных данных и выполняет следующие действия:
     * 1. Удаляет пробелы в начале и конце строки с помощью `trim()`.
     * 2. Заменяет последовательности пробелов внутри строки на одиночные пробелы с помощью `preg_replace()`.
     * 3. Если в результате строка становится пустой, возвращает `null`, иначе возвращает обрезанную строку.
     *
     * @param string $input Входная строка, которую необходимо обрезать.
     * @return string|null Возвращает обрезанную строку или `null`, если строка стала пустой.
     */
    public static function trimString(string $input): ?string
    {
        $trimmed = trim($input);
        $trimmed = preg_replace('/\s+/', ' ', $trimmed);
        return $trimmed === '' ? null : $trimmed;
    }


    /**
     * Проверяет массив вопросов на наличие незначительных ошибок.
     *
     * Эта функция принимает массив вопросов и проверяет каждый вопрос на наличие следующих ошибок:
     * 1. Страница вопроса создана, но не заполнена (пустые поля `question` и `answers`).
     * 2. Создано поле варианта ответа, но оно не заполнено (значение `null`), при этом этот вариант ответа не является правильным.
     *
     * Если для какого-либо вопроса обнаружены ошибки, то для этого вопроса формируется массив с информацией об ошибках.
     * Результирующий массив содержит информацию обо всех вопросах с ошибками.
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен в виде ассоциативного массива с ключами:
     *                         - `id`: уникальный идентификатор вопроса
     *                         - `question`: строка с текстом вопроса или `null`
     *                         - `answers`: массив строк с вариантами ответов или массив, содержащий `null`
     *                         - `correctAnswerIndex`: целое число, индекс правильного ответа в массиве `answers`
     * @return array Возвращает массив с информацией о вопросах, в которых были обнаружены незначительные ошибки.
     *               Каждый элемент результирующего массива - ассоциативный массив с ключами:
     *               - `id_question`: уникальный идентификатор вопроса
     *               - `errors`: массив ошибок, где каждая ошибка представлена в виде ассоциативного массива с ключами:
     *                 - `id_error`: уникальный идентификатор ошибки
     *                 - `text_error`: текстовое описание ошибки
     */
    public static function checkDataForMinorErrors(array $questions): array
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
     * Фильтрует массив вопросов, удаляя пустые вопросы, и при необходимости пересчитывает индексы.
     *
     * Эта функция принимает массив вопросов и выполняет следующие действия:
     * 1. Фильтрует массив вопросов, удаляя те вопросы, у которых поле `question` имеет значение `null` и все поля `answers` также имеют значение `null`.
     * 2. Если после фильтрации массив вопросов оказался пустым, возвращает массив, содержащий первый вопрос из исходного массива.
     * 3. Если параметр `$reindex` равен `true`, пересчитывает индексы оставшихся вопросов и обновляет значение поля `id` для каждого вопроса.
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен в виде ассоциативного массива с ключами:
     *                         - `id`: уникальный идентификатор вопроса
     *                         - `question`: строка с текстом вопроса или `null`
     *                         - `answers`: массив строк с вариантами ответов или массив, содержащий `null`
     *                         - `correctAnswerIndex`: целое число, индекс правильного ответа в массиве `answers`
     * @param bool $reindex Флаг, указывающий, нужно ли пересчитывать индексы оставшихся вопросов.
     * @return array Возвращает отфильтрованный массив вопросов. Если после фильтрации массив оказался пустым, возвращает массив, содержащий первый вопрос из исходного массива.
     */
    public static function filterQuestions(array $questions, bool $reindex): array
    {
        // Фильтрация вопросов
        $filteredQuestions = array_filter($questions, function ($question) {
            return !(
                is_null($question['question']) && (empty($question['answers'])
                    || array_filter($question['answers'], function ($answer) {
                        return !is_null($answer);
                    }) === [])
            );
        });

        // Если все вопросы отфильтрованы, оставляем первый вопрос
        if (empty($filteredQuestions)) {
            return [reset($questions)];
        }

        // Пересчитываем индексы массива, если метка $reindex равна true
        if ($reindex) {
            $filteredQuestions = array_values($filteredQuestions);

            // Обновляем id вопросов в соответствии с новыми индексами
            foreach ($filteredQuestions as $index => &$question) {
                $question['id'] = $index + 1; // id начинаются с 1
            }
        }

        return $filteredQuestions;
    }


    /**
     * Фильтрует массив вопросов, удаляя пустые варианты ответов и обновляя индекс правильного ответа.
     *
     * Эта функция принимает массив вопросов и выполняет следующие действия для каждого вопроса:
     * 1. Проверяет, есть ли правильный ответ для данного вопроса.
     * 2. Если правильного ответа нет, возвращает вопрос с отфильтрованным массивом ответов (без `null`-значений) и `correctAnswerIndex` равным `null`.
     * 3. Если правильный ответ есть, фильтрует массив ответов, оставляя все ненулевые значения, кроме правильного ответа.
     * 4. Пересчитывает индекс правильного ответа в отфильтрованном массиве ответов.
     * 5. Возвращает вопрос с отфильтрованными ответами и обновленным индексом правильного ответа.
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен в виде ассоциативного массива с ключами:
     *                         - `id`: уникальный идентификатор вопроса
     *                         - `question`: строка с текстом вопроса
     *                         - `answers`: массив строк с вариантами ответов или массив, содержащий `null`
     *                         - `correctAnswerIndex`: целое число, индекс правильного ответа в массиве `answers` или `null`
     * @return array Возвращает массив вопросов с отфильтрованными вариантами ответов и обновленными индексами правильных ответов.
     */
    public static function filterAnswers(array $questions): array
    {
        return array_map(function ($question) {
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
            $filteredAnswers = array_filter($answers, function ($answer, $index) use ($correctAnswerIndex) {
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
     * Проверяет массив вопросов на наличие логических ошибок, таких как дублирование вопросов и вариантов ответов.
     *
     * Эта функция выполняет следующие проверки:
     * 1. Проверяет, есть ли в массиве вопросы с одинаковым текстом. Если да, формирует ошибки для этих вопросов.
     * 2. Проверяет, есть ли в каждом вопросе дублирующиеся варианты ответов. Если да, формирует ошибки для этих вопросов.
     * 3. Возвращает массив ошибок в формате, удобном для дальнейшего использования.
     *
     * Каждая ошибка представлена в виде ассоциативного массива со следующими ключами:
     * - `id_question`: уникальный идентификатор вопроса, в котором обнаружена ошибка
     * - `errors`: массив ошибок для данного вопроса, где каждая ошибка представлена в виде ассоциативного массива:
     *   - `id_error`: уникальный идентификатор типа ошибки (1 - дублирование вопроса, 2 - дублирование ответа)
     *   - `text_error`: текстовое описание ошибки
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен в виде ассоциативного массива с ключами:
     *                         - `id`: уникальный идентификатор вопроса
     *                         - `question`: строка с текстом вопроса или `null`
     *                         - `answers`: массив строк с вариантами ответов или массив, содержащий `null`
     *                         - `correctAnswerIndex`: целое число, индекс правильного ответа в массиве `answers` или `null`
     * @return array Возвращает массив ошибок, обнаруженных в массиве вопросов.
     */
    public static function checkDataForLogicalErrors(array $questions): array
    {
        $errors = [];

        // Проверка на дублирование вопросов
        $questionTexts = [];
        foreach ($questions as $question) {
            $questionText = $question['question'];

            // Игнорируем вопросы с текстом null
            if ($questionText !== null) {
                if (isset($questionTexts[$questionText])) {
                    $questionTexts[$questionText][] = $question['id'];
                } else {
                    $questionTexts[$questionText] = [$question['id']];
                }
            }
        }

        // Формирование ошибок для дублированных вопросов
        foreach ($questionTexts as $text => $ids) {
            if (count($ids) > 1) {
                foreach ($ids as $id) {
                    $errors[$id][] = [
                        "id_error" => 1,
                        "text_error" => "Встречаются одинаковые вопросы в викторине: №" . implode(", №", $ids) . "."
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
                        "text_error" => "Встречаются одинаковые варианты ответов в полях: №" . implode(", №", $indices) . "."
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
     * Исправляет логические ошибки в массиве вопросов, такие как дублирование вопросов и вариантов ответов.
     *
     * Эта функция выполняет следующие действия:
     * 1. Удаляет дублирующиеся вопросы, оставляя только уникальные вопросы.
     * 2. Для каждого вопроса удаляет дублирующиеся варианты ответов.
     * 3. Пересчитывает индекс правильного ответа в соответствии с новым массивом ответов.
     * 4. Пересчитывает идентификаторы вопросов, начиная с 1.
     * 5. Возвращает массив вопросов с исправленными ошибками.
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен в виде ассоциативного массива с ключами:
     *                         - `id`: уникальный идентификатор вопроса
     *                         - `question`: строка с текстом вопроса или `null`
     *                         - `answers`: массив строк с вариантами ответов или массив, содержащий `null`
     *                         - `correctAnswerIndex`: целое число, индекс правильного ответа в массиве `answers` или `null`
     * @return array Возвращает массив вопросов с исправленными логическими ошибками.
     */
    public static function fixLogicalErrors(array $questions): array
    {
        $uniqueQuestions = [];
        $questionTexts = [];

        // Удаление дублирующихся вопросов с учетом null
        foreach ($questions as $question) {
            $questionText = $question['question'];

            // Проверяем, что вопрос не равен null и не дублируется
            if ($questionText !== null && !in_array($questionText, $questionTexts)) {
                $questionTexts[] = $questionText;
                $uniqueQuestions[] = $question; // Сохраняем уникальный вопрос
            } else {
                // Если вопрос равен null, добавляем его в уникальные вопросы
                if ($questionText === null) {
                    $uniqueQuestions[] = $question;
                }
            }
        }

        // Удаление дублирующихся ответов и пересчет индексов с учетом null
        foreach ($uniqueQuestions as &$question) {
            $answerTexts = [];
            $uniqueAnswers = [];
            $correctAnswerIndex = $question['correctAnswerIndex'];

            foreach ($question['answers'] as $index => $answerText) {
                // Проверяем, что ответ не равен null и не дублируется
                if ($answerText !== null && !in_array($answerText, $answerTexts)) {
                    $answerTexts[] = $answerText;
                    $uniqueAnswers[] = $answerText;
                } else {
                    // Если ответ равен null, добавляем его в уникальные ответы
                    if ($answerText === null) {
                        $uniqueAnswers[] = $answerText;
                    }
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
     * Проверяет массив вопросов на наличие критических ошибок, которые делают невозможным корректную работу викторины.
     *
     * Эта функция выполняет следующие проверки:
     * 1. Проверяет, что массив вопросов не пустой. Если массив пустой, возвращает ошибку с кодом 5.
     * 2. Для каждого вопроса проверяет:
     *    - Что текст вопроса не равен null (код ошибки 1).
     *    - Что количество вариантов ответов больше или равно 2 (код ошибки 2).
     *    - Что индекс правильного ответа не равен null (код ошибки 3).
     *    - Что правильный ответ не равен null (код ошибки 4).
     * 3. Возвращает массив ошибок в формате, удобном для дальнейшего использования.
     *
     * Каждая ошибка представлена в виде ассоциативного массива со следующими ключами:
     * - `id_question`: уникальный идентификатор вопроса, в котором обнаружена ошибка
     * - `errors`: массив ошибок для данного вопроса, где каждая ошибка представлена в виде ассоциативного массива:
     *   - `id_error`: уникальный идентификатор типа ошибки (1 - null в тексте вопроса, 2 - недостаточное количество ответов, 3 - null в индексе правильного ответа, 4 - null в правильном ответе, 5 - пустой массив вопросов)
     *   - `text_error`: текстовое описание ошибки
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен в виде ассоциативного массива с ключами:
     *                         - `id`: уникальный идентификатор вопроса
     *                         - `question`: строка с текстом вопроса или `null`
     *                         - `answers`: массив строк с вариантами ответов или массив, содержащий `null`
     *                         - `correctAnswerIndex`: целое число, индекс правильного ответа в массиве `answers` или `null`
     * @return array Возвращает массив ошибок, обнаруженных в массиве вопросов.
     */
    public static function checkDataForCriticalErrors(array $questions): array
    {
        $result = [];

        // Проверка на пустой массив вопросов
        if (empty($questions)) {
            $result[] = [
                "id_question" => 1,
                "errors" => [
                    ["id_error" => 5, "text_error" => "В викторине должно быть как минимум одна не пустая страница вопроса."]
                ]
            ];
            return $result; // Возвращаем результат сразу, так как дальнейшие проверки не имеют смысла
        }

        foreach ($questions as $question) {
            $errors = [];

            // Проверка на null для question
            if (is_null($question['question'])) {
                $errors[] = ["id_error" => 1, "text_error" => "Вопрос не должен быть null."];
            }

            // Проверка на количество ответов
            if (count($question['answers']) < 2) {
                $errors[] = ["id_error" => 2, "text_error" => "Должно быть как минимум 2 ответа."];
            }

            // Проверка на null для correctAnswerIndex
            if (is_null($question['correctAnswerIndex'])) {
                $errors[] = ["id_error" => 3, "text_error" => "Индекс правильного ответа не может быть null."];
            }

            if (!is_null($question['correctAnswerIndex'])) {
                if (is_null($question['answers'][$question['correctAnswerIndex']])) {
                    $errors[] = ["id_error" => 4, "text_error" => "Правильный ответ не может быть null."];
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
     * Проверяет название викторины на наличие ошибок и возвращает массив обнаруженных ошибок.
     *
     * Эта функция выполняет следующие проверки:
     * 1. Очищает строку с названием викторины от лишних пробелов, табуляций и переносов строк.
     * 2. Удаляет лишние пробелы внутри строки.
     * 3. Проверяет, что длина очищенного названия викторины не меньше 5 символов (критическая ошибка).
     * 4. Проверяет, что в названии викторины нет двух и более подряд идущих пробелов (косметическая ошибка).
     *
     * Функция возвращает массив ошибок, где ключи массива соответствуют типу ошибки:
     * - `critical_error`: критическая ошибка, которая делает невозможным корректную работу викторины.
     * - `cosmetic_error`: косметическая ошибка, которая не влияет на работу викторины, но может быть исправлена для улучшения внешнего вида.
     *
     * @param string $quizName Название викторины в виде строки.
     * @return array Возвращает массив ошибок, обнаруженных в названии викторины.
     */
    public static function checkNameQuizForErrors(string $quizName): array
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


    /**
     * Сохраняет викторину и её вопросы в базу данных.
     *
     * Этот метод выполняет следующие действия:
     * 1. Создаёт запись викторины в таблице `quizzes`, используя переданное название викторины.
     * 2. Генерирует уникальный идентификатор для викторины с помощью `Uuid::uuid4()`.
     * 3. Сохраняет каждый вопрос викторины в таблице `quiz_question_answers`, включая текст вопроса, правильный ответ и неправильные варианты ответов.
     * 4. Использует транзакцию для обеспечения атомарности операций: если произойдёт ошибка при сохранении, все изменения будут отменены.
     *
     * @param string $quizName Название викторины, которое будет сохранено в таблице `quizzes`.
     * @param array $questions Массив вопросов викторины, где каждый вопрос представлен в виде ассоциативного массива с ключами:
     *                         - `question`: текст вопроса.
     *                         - `answers`: массив вариантов ответов.
     *                         - `correctAnswerIndex`: индекс правильного ответа в массиве `answers`.
     * @param string $login_user Логин пользователя, который создаёт викторину
     * @return void Метод не возвращает значение, но сохраняет данные в базу данных.
     */
    public static function saveQuizToDatabase(string $quizName, array $questions, string $login_user): void
    {
        DB::transaction(function () use ($quizName, $questions, $login_user) {
            // Занесение в бд название викторины (табл. quizzes)
            $quiz = Quiz::create([
                'id_quiz' => Uuid::uuid4()->toString(),
                'name_quiz' => $quizName,
                'is_ready' => true,
                'login_user' => $login_user,
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
