<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreateQuizController extends Controller
{
    public function questions(Request $request): \Illuminate\Http\JsonResponse
    {
        // Получаем данные из запроса
        $questions = $request->all();

        $questionsEdited = $this->quizDataEditor($questions);
        $errors = $this->validateQuestions($questionsEdited);

        // Возвращаем ответ клиенту
        return response()->json([
            'questionsEdited' => $questionsEdited,
            'errors' => $errors
        ], 200);
    }


    /**
     * Проверяет массив вопросов на наличие ошибок (валидация вопросов).
     *
     * @param array $questions Массив вопросов.
     * @return array Возвращает массив с ошибками для каждого вопроса.
     */
    private function validateQuestions(array $questions): array
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
                    "id_question" => $question['id'], // Используем поле id
                    "errors" => $errors
                ];
            }
        }

        return $result;
    }


    /**
     * Объединяет фильтрацию вопросов, фильтрацию ответов, обрезку текстовых полей через Trim и новую индексацию вопросов.
     *
     * @param array $questions Массив вопросов.
     * @return array Отфильтрованный массив вопросов.
     */
    private function quizDataEditor(array $questions): array
    {
        $trimmedQuestions = $this->trimQuestions($questions); //'Trim' полей вопросов
        $questionsWithFilteredAnswers = $this->filterAnswers($trimmedQuestions); //Фильтрация вариантов ответов вопросов
        $filteredQuestions = $this->filterQuestions($questionsWithFilteredAnswers); //Фильтрация вопросов
        $newQuestions= $this->renumberQuestions($filteredQuestions); //Новая индексация вопросов

        return $newQuestions;
    }


    /**
     * Фильтрует массив вопросов, удаляя те, у которых отсутствует текст вопроса
     * и нет ненулевых ответов.
     *
     * @param array $questions Массив вопросов, где каждый вопрос представлен
     *                        ассоциативным массивом с ключами 'question' и 'answers'.
     *
     * @return array Отфильтрованный массив вопросов, содержащий только те
     *               вопросы, которые имеют ненулевой текст вопроса или хотя бы
     *               один ненулевой ответ.
     */
    private function filterQuestions(array $questions): array
    {
        return array_filter($questions, function($question) {
            return !(
                is_null($question['question']) && (empty($question['answers'])
                    || array_filter($question['answers'], function($answer){
                        return !is_null($answer);}) === [])
            );
        });
    }

    /**
     * Удаляет элементы из массива ответов каждого вопроса, которые равны null,
     * кроме элемента с индексом correctAnswerIndex (если сам correctAnswerIndex не равен null).
     *
     * @param array $questions Массив вопросов, где каждый вопрос содержит 'answers' и 'correctAnswerIndex'.
     * @return array Массив отфильтрованных вопросов с обновленными индексами правильного ответа.
     */
    private function filterAnswers(array $questions): array
    {
        return array_map(function($question) {
            $answers = $question['answers'];
            $correctAnswerIndex = $question['correctAnswerIndex'];

            // Сохраняем правильный ответ (если он не null)
            $correctAnswer = $answers[$correctAnswerIndex];

            // Фильтруем массив, оставляя все ненулевые значения, кроме правильного ответа
            $filteredAnswers = array_filter($answers, function($answer, $index) use ($correctAnswerIndex) {
                return !is_null($answer) || $index === $correctAnswerIndex;
            }, ARRAY_FILTER_USE_BOTH);

            //Изменяем индексацию под новый массив
            $filteredAnswers = array_values($filteredAnswers);

            // Пересчитываем новый индекс правильного ответа в отфильтрованном массиве
            $newCorrectAnswerIndex = array_search($correctAnswer, $filteredAnswers, true);

            // Возвращаем вопрос с отфильтрованными ответами и обновленным индексом
            return [
                'question' => $question['question'],
                'answers' => array_values($filteredAnswers), // Приводим массив к индексам 0, 1, 2...
                'correctAnswerIndex' => $newCorrectAnswerIndex,
            ];
        }, $questions);
    }

    /**
     * Очищает массив вопросов, удаляя лишние пробелы, табуляции и знаки переноса.
     *
     * Для каждого вопроса:
     * - Применяет trim к полю question.
     * - Применяет trim ко всем элементам массива answers.
     * - Устанавливает значение поля question в null, если оно пустое после очистки.
     * - Устанавливает элементы массива answers в null, если они пустые после очистки.
     *
     * @param array $questions Массив вопросов, где каждый вопрос содержит 'question' и 'answers'.
     * @return array Очищенный массив вопросов.
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
     * Перенумеровывает индекс вопросов в массиве.
     *
     * @param array $questions Массив вопросов.
     * @return array Массив вопросов с новой индексацией.
     */
    public function renumberQuestions(array $questions): array
    {
        $renumberedQuestions = [];
        $index = 1; // Начинаем с 1

        foreach ($questions as $question) {
            // Добавляем новый вопрос с обновленным id
            $renumberedQuestions[] = [
                'id' => $index,
                'question' => $question['question'] ?? null,
                'answers' => $question['answers'] ?? [],
                'correctAnswerIndex' => $question['correctAnswerIndex'] ?? null,
            ];
            $index++; // Увеличиваем индекс
        }

        return $renumberedQuestions;
    }


    // Публичный метод для доступа к редактору вопросов
    public function getQuestionsAfterQuizDataEditor(array $questions): array
    {
        return $this->quizDataEditor($questions);
    }

    // Публичный метод для доступа к 'триму' вопросов
    public function getTrimQuestions(array $questions): array
    {
        return $this->trimQuestions($questions);
    }

    // Публичный метод для доступа к фильтрации ответов вопросов
    public function getQuestionsWithFilteredAnswers(array $questions): array
    {
        return $this->filterAnswers($questions);
    }

    // Публичный метод для доступа к фильтрации вопросов
    public function getFilteredQuestions(array $questions): array
    {
        return $this->filterQuestions($questions);
    }

    // Публичный метод для доступа валидации вопросов
    public function getValidateQuestions(array $questions): array
    {
        return $this->validateQuestions($questions);
    }


}

// Записываем данные в базу данных
//        $quiz = Quiz::create([
//            'name_quiz' => 'Викторина №2',
//            'id_user' => null,
//        ]);
