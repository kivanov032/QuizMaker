<?php

namespace Tests\Feature;

use Tests\TestCase;

class CreatorQuizControllerTest extends TestCase
{
    /**
     * --- Тестирование метода searchQuizErrors ---
     */

    /**
     * Тест успешного анализа викторины без ошибок.
     */
    public function test_search_quiz_errors_successful_analysis(): void
    {
        // Подготовка данных
        $quizName = 'Тестовая викторина';
        $questions = [
            [
                'id' => 1,
                'question' => 'Какой язык программирования используется в Laravel?',
                'answers' => ['JavaScript', 'PHP', 'Python', 'Java'],
                'correctAnswerIndex' => 1,
            ],
        ];

        // Вызов метода
        $response = $this->postJson('/api/search-quiz-errors', [
            'quizName' => $quizName,
            'questions' => $questions,
        ]);

        // Проверка статуса ответа и структуры JSON
        $response->assertStatus(200)
            ->assertJsonStructure([
                'cosmetic_errors',
                'minor_errors',
                'critical_errors',
                'logical_errors',
                'name_quiz_errors',
            ])
            ->assertJson([
                'cosmetic_errors' => [],
                'minor_errors' => [],
                'critical_errors' => [],
                'logical_errors' => [],
                'name_quiz_errors' => [],
            ]);
    }

    /**
     * Тест анализа викторины с критическими ошибками.
     */
    public function test_search_quiz_errors_with_critical_errors(): void
    {
        // Подготовка данных с критическими ошибками
        $quizName = '';
        $questions = [
            [
                'id' => 1,
                'question' => null,
                'answers' => [],
                'correctAnswerIndex' => null,
            ],
        ];

        // Вызов метода
        $response = $this->postJson('/api/search-quiz-errors', [
            'quizName' => $quizName,
            'questions' => $questions,
        ]);

        // Проверка статуса ответа и структуры JSON
        $response->assertStatus(200)
            ->assertJsonStructure([
                'cosmetic_errors',
                'minor_errors',
                'critical_errors',
                'logical_errors',
                'name_quiz_errors',
            ])
            ->assertJson([
                'critical_errors' => [
                    [
                        'id_question' => 1,
                        'errors' => [
                            [
                                'id_error' => 1,
                                'text_error' => 'Вопрос не должен быть null.',
                            ],
                            [
                                'id_error' => 2,
                                'text_error' => 'Должно быть как минимум 2 ответа.',
                            ],
                            [
                                'id_error' => 3,
                                'text_error' => 'Индекс правильного ответа не может быть null.',
                            ],
                        ],
                    ],
                ],
                'name_quiz_errors' => [
                    "critical_error" => "В названии викторины должно быть хотя бы 5 символов."
                ],
            ]);
    }

    /**
     * Тест анализа викторины с логическими ошибками.
     */
    public function test_search_quiz_errors_with_logical_errors(): void
    {
        // Подготовка данных с логическими ошибками
        $quizName = 'Тестовая викторина';
        $questions = [
            [
                'id' => 1,
                'question' => 'Какой язык программирования используется в Laravel?',
                'answers' => ['PHP', 'PHP', 'Python', 'Java'],
                'correctAnswerIndex' => 0,
            ],
        ];

        // Вызов метода
        $response = $this->postJson('/api/search-quiz-errors', [
            'quizName' => $quizName,
            'questions' => $questions,
        ]);

        // Проверка статуса ответа и структуры JSON
        $response->assertStatus(200)
            ->assertJsonStructure([
                'cosmetic_errors',
                'minor_errors',
                'critical_errors',
                'logical_errors',
                'name_quiz_errors',
            ])
            ->assertJson([
                'logical_errors' => [
                    [
                        'id_question' => 1,
                        'errors' => [
                            [
                                'id_error' => 2,
                                'text_error' => 'Встречаются одинаковые варианты ответов в полях: №1, №2.',
                            ],
                        ],
                    ],
                ],
            ]);
    }


    /**
     * --- Тестирование метода fixQuizErrors ---
     */

    /**
     * Тест успешного исправления ошибок с возвращением исправленных данных.
     */
    public function test_fix_quiz_errors_successful_fix(): void
    {
        // Подготовка данных
        $quizName = '  Тестовая викторина  ';
        $questions = [
            [
                'id' => 1,
                'question' => '  Какой язык программирования используется в Laravel?  ',
                'answers' => ['JavaScript', 'PHP', 'Python', 'Java'],
                'correctAnswerIndex' => 1,
            ],
        ];
        $errors = [
            'cosmeticErrors' => true,
            'minorErrors' => false,
            'logicalErrors' => false,
            'cosmeticErrorQuizName' => true,
        ];
        $searchQuizErrors_flag = false;

        // Вызов метода
        $response = $this->postJson('/api/fix-quiz-errors', [
            'quizName' => $quizName,
            'questions' => $questions,
            'errors' => $errors,
            'searchQuizErrors_flag' => $searchQuizErrors_flag,
        ]);

        // Проверка статуса ответа и структуры JSON
        $response->assertStatus(200)
            ->assertJson([
                'questions' => [
                    [
                        'id' => 1,
                        'question' => 'Какой язык программирования используется в Laravel?',
                        'answers' => ['JavaScript', 'PHP', 'Python', 'Java'],
                        'correctAnswerIndex' => 1,
                    ],
                ],
                'quizName' => 'Тестовая викторина',
            ]);
    }

    /**
     * Тест успешного исправления ошибок с возвращением списка ошибок.
     */
    public function test_fix_quiz_errors_successful_fix_with_errors(): void
    {
        // Подготовка данных
        $quizName = '  Тестовая викторина  ';
        $questions = [
            [
                'id' => 1,
                'question' => '  Какой язык программирования используется в Laravel?  ',
                'answers' => ['JavaScript', 'PHP', 'Python', 'Java'],
                'correctAnswerIndex' => 1,
            ],
        ];
        $errors = [
            'cosmeticErrors' => true,
            'minorErrors' => false,
            'logicalErrors' => false,
            'cosmeticErrorQuizName' => true,
        ];
        $searchQuizErrors_flag = true;

        // Вызов метода
        $response = $this->postJson('/api/fix-quiz-errors', [
            'quizName' => $quizName,
            'questions' => $questions,
            'errors' => $errors,
            'searchQuizErrors_flag' => $searchQuizErrors_flag,
        ]);

        // Проверка статуса ответа и структуры JSON
        $response->assertStatus(200)
            ->assertJsonStructure([
                'questions',
                'quizName',
                'errors' => [
                    'cosmetic_errors',
                    'minor_errors',
                    'critical_errors',
                    'logical_errors',
                    'name_quiz_errors',
                ],
            ]);
    }

    /**
     * Тест исправления ошибок с логическими ошибками.
     */
    public function test_fix_quiz_errors_with_logical_errors(): void
    {
        // Подготовка данных с логическими ошибками
        $quizName = 'Тестовая викторина';
        $questions = [
            [
                'id' => 1,
                'question' => 'Какой язык программирования используется в Laravel?',
                'answers' => ['PHP', 'PHP', 'Python', 'Java'],
                'correctAnswerIndex' => 0,
            ],
        ];
        $errors = [
            'cosmeticErrors' => false,
            'minorErrors' => false,
            'logicalErrors' => true,
            'cosmeticErrorQuizName' => false,
        ];
        $searchQuizErrors_flag = false;

        // Вызов метода
        $response = $this->postJson('/api/fix-quiz-errors', [
            'quizName' => $quizName,
            'questions' => $questions,
            'errors' => $errors,
            'searchQuizErrors_flag' => $searchQuizErrors_flag,
        ]);

        // Проверка статуса ответа и структуры JSON
        $response->assertStatus(200)
            ->assertJson([
                'questions' => [
                    [
                        'id' => 1,
                        'question' => 'Какой язык программирования используется в Laravel?',
                        'answers' => ['PHP', 'Python', 'Java'],
                        'correctAnswerIndex' => 0,
                    ],
                ],
                'quizName' => 'Тестовая викторина',
            ]);
    }


    /**
     * --- Тестирование метода createQuiz ---
     */

    /**
     * Тест успешного создания викторины.
     */
    public function test_create_quiz_successful_creation(): void
    {
        // Подготовка данных
        $quizName = 'Тестовая викторина';
        $questions = [
            [
                'id' => 1,
                'question' => 'Какой язык программирования используется в Laravel?',
                'answers' => ['JavaScript', 'PHP', 'Python', 'Java'],
                'correctAnswerIndex' => 1,
            ],
        ];
        $errors = [
            'cosmeticErrors' => false,
            'minorErrors' => false,
            'logicalErrors' => false,
            'cosmeticErrorQuizName' => false,
        ];
        $login = 'k12345a';

        // Вызов метода
        $response = $this->postJson('/api/create-quiz', [
            'quizName' => $quizName,
            'questions' => $questions,
            'errors' => $errors,
            'login' => $login,
        ]);

        // Проверка статуса ответа и структуры JSON
        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'operation_index' => 1,
            ]);
    }

    /**
     * Тест создания викторины с исправлением ошибок.
     */
    public function test_create_quiz_with_error_fixing(): void
    {
        // Подготовка данных с ошибками
        $quizName = '  Тестовая викторина  ';
        $questions = [
            [
                'id' => 1,
                'question' => '  Какой язык программирования используется в Laravel?  ',
                'answers' => ['JavaScript', 'PHP', 'Python', 'Java'],
                'correctAnswerIndex' => 1,
            ],
        ];
        $errors = [
            'cosmeticErrors' => true,
            'minorErrors' => false,
            'logicalErrors' => false,
            'cosmeticErrorQuizName' => true,
        ];
        $login = 'k12345a';

        // Вызов метода
        $response = $this->postJson('/api/create-quiz', [
            'quizName' => $quizName,
            'questions' => $questions,
            'errors' => $errors,
            'login' => $login,
        ]);

        // Проверка статуса ответа и структуры JSON
        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'operation_index' => 1,
            ]);
    }

    /**
     * Тест создания викторины с ошибками валидации.
     */
    public function test_create_quiz_with_validation_errors(): void
    {
        // Подготовка данных с ошибками валидации
        $quizName = 'Тестовая викторина';
        $questions = []; // Пустой массив вопросов
        $errors = [
            'cosmeticErrors' => false,
            'minorErrors' => false,
            'logicalErrors' => false,
            'cosmeticErrorQuizName' => false,
        ];
        $login = 'k12345a';

        // Вызов метода
        $response = $this->postJson('/api/create-quiz', [
            'quizName' => $quizName,
            'questions' => $questions,
            'errors' => $errors,
            'login' => $login,
        ]);

        // Проверка статуса ответа и структуры JSON
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Поле questions обязательно для заполнения.',
                'errors' => [
                    'questions' => [
                        'Поле questions обязательно для заполнения.',
                    ],
                ],
            ]);
    }

}








