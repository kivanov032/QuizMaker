<?php

namespace Tests\Unit;

use App\Helpers\CreatorQuizHelper;
use PHPUnit\Framework\TestCase;

class CreatorQuizHelperTest extends TestCase
{
    /**
     * Тест на функцию, которая обращает фактически пустые поля в null
     */
    public function test_CleanEmptyFields(): void
    {
        $questions = [
            ["id" => 1, "question" => "   \n\t", 'answers' => ["John", "   ", "\t\n", "Doe"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => "What is your name?", "answers" => ["Alice", "Bob", "   ", "\n"], "correctAnswerIndex" => 1],
            ["id" => 3, "question" => "What is your age?", "answers" => ["20", "30", "40"], "correctAnswerIndex" => 0],
            ["id" => 4, "question" => "   ", "answers" => ["   ", "\n", "   "], "correctAnswerIndex" => 0],
            ["id" => 5, "question" => "   ", "answers" => ["Answer 1", "   "], "correctAnswerIndex" => 0],
            ["id" => 6, "question" => null, "answers" => [], "correctAnswerIndex" => 0]
        ];

        $expectedResult = [
            ["id" => 1, "question" => null, "answers" => ["John", null, null, "Doe"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => "What is your name?", "answers" => ["Alice", "Bob", null, null], "correctAnswerIndex" => 1],
            ["id" => 3, "question" => "What is your age?", "answers" => ["20", "30", "40"], "correctAnswerIndex" => 0],
            ["id" => 4, "question" => null, "answers" => [null, null, null], "correctAnswerIndex" => 0],
            ["id" => 5, "question" => null, "answers" => ["Answer 1", null], "correctAnswerIndex" => 0],
            ["id" => 6, "question" => null, "answers" => [], "correctAnswerIndex" => 0]
        ];

        $result = CreatorQuizHelper::cleanEmptyFields($questions);
        $this->assertEquals($expectedResult, $result);
    }


    /**
     * Тест на поиск косметических ошибок
     */
    public function test_CheckDataForCosmeticErrors(): void
    {
        $questions = [
            ["id" => 1, "question" => "1  2", "answers" => ["1", "2", "3"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => "1 2", "answers" => ["1", "2  3", "3"], "correctAnswerIndex" => 0],
            ["id" => 3, "question" => "1  2", "answers" => ["3  3", "2  2", "4"], "correctAnswerIndex" => 0],
            ["id" => 4, "question" => "1   2", "answers" => ["3  3", "2 2 2", "4  4"], "correctAnswerIndex" => 0],
            ["id" => 5, "question" => "  ", "answers" => ["  ", "2 2 2", "4  4"], "correctAnswerIndex" => 0],
            ["id" => 6, "question" => "1", "answers" => ["2", "2 2 2", "4 4"], "correctAnswerIndex" => 0],
        ];

        $expectedErrors = [
            [
                "id_question" => 1,
                "errors" => [
                    [
                        "id_error" => 1,
                        "text_error" => "В вопросе викторины обнаружены лишние пробелы."
                    ]
                ]
            ],
            [
                "id_question" => 2,
                "errors" => [
                    [
                        "id_error" => 2,
                        "text_error" => "В поле варианта ответа №2 обнаружены лишние пробелы."
                    ]
                ]
            ],
            [
                "id_question" => 3,
                "errors" => [
                    [
                        "id_error" => 1,
                        "text_error" => "В вопросе викторины обнаружены лишние пробелы."
                    ],
                    [
                        "id_error" => 2,
                        "text_error" => "В поле варианта ответа №1 обнаружены лишние пробелы."
                    ],
                    [
                        "id_error" => 2,
                        "text_error" => "В поле варианта ответа №2 обнаружены лишние пробелы."
                    ]
                ]
            ],
            [
                "id_question" => 4,
                "errors" => [
                    [
                        "id_error" => 1,
                        "text_error" => "В вопросе викторины обнаружены лишние пробелы."
                    ],
                    [
                        "id_error" => 2,
                        "text_error" => "В поле варианта ответа №1 обнаружены лишние пробелы."
                    ],
                    [
                        "id_error" => 2,
                        "text_error" => "В поле варианта ответа №3 обнаружены лишние пробелы."
                    ]
                ]
            ],
            [
                "id_question" => 5,
                "errors" => [
                    [
                        "id_error" => 1,
                        "text_error" => "В вопросе викторины обнаружены лишние пробелы."
                    ],
                    [
                        "id_error" => 2,
                        "text_error" => "В поле варианта ответа №1 обнаружены лишние пробелы."
                    ],
                    [
                        "id_error" => 2,
                        "text_error" => "В поле варианта ответа №3 обнаружены лишние пробелы."
                    ]
                ]
            ]
        ];

        $cosmetic_errors = CreatorQuizHelper::checkDataForCosmeticErrors($questions);
        $this->assertEquals($expectedErrors, $cosmetic_errors);
    }


    /**
     * Тест на 'трим' вопросов
     */
    public function test_TrimQuestions(): void
    {
        $questions = [
            ["id" => 1, "question" => "   What is your name?   ", "answers" => ["John", "   ", "Doe"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => "   ", "answers" => ["  ", "25", ""], "correctAnswerIndex" => 0],
            ["id" => 3, "question" => "", "answers" => ["", "   ", ""], "correctAnswerIndex" => 0],
            ["id" => 4, "question" => "How old are you?", "answers" => ["  ", "", "30"], "correctAnswerIndex" => 0],
            ["id" => 5, "question" => "What is your favorite color?", "answers" => ["Red", "Blue", "Green"], "correctAnswerIndex" => 0],
            ["id" => 6, "question" => "   What is your name?\n   ", "answers" => ["John\n", "   ", "Doe"], "correctAnswerIndex" => 0],
            ["id" => 7, "question" => "\n   ", "answers" => ["\n", "25", "\r\n"], "correctAnswerIndex" => 0],
            ["id" => 8,"question" => "1   3", "answers" => ["3","4","5"], "correctAnswerIndex" => null]
        ];

        $expected = [
            ["id" => 1, "question" => "What is your name?", "answers" => ["John", null, "Doe"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => null, "answers" => [null, "25", null], "correctAnswerIndex" => 0],
            ["id" => 3, "question" => null, "answers" => [null, null, null], "correctAnswerIndex" => 0],
            ["id" => 4, "question" => "How old are you?", "answers" => [null, null, "30"], "correctAnswerIndex" => 0],
            ["id" => 5, "question" => "What is your favorite color?", "answers" => ["Red", "Blue", "Green"], "correctAnswerIndex" => 0],
            ["id" => 6, "question" => "What is your name?", "answers" => ["John", null, "Doe"], "correctAnswerIndex" => 0],
            ["id" => 7, "question" => null, "answers" => [null, "25", null], "correctAnswerIndex" => 0],
            ["id" => 8,"question" => "1 3","answers" => ["3","4","5"], "correctAnswerIndex" => null]
        ];

        $result = CreatorQuizHelper::trimQuestions($questions);

        $this->assertEquals($expected, $result);
    }


    /**
     * Тест на обрезку строк
     */
    public function test_TrimString(): void
    {
        $testCases = [
            // Тестовые случаи с ожидаемыми результатами
            ["input" => "   Hello World   ", "expected" => "Hello World"],
            ["input" => "  Multiple    spaces  ", "expected" => "Multiple spaces"],
            ["input" => "SingleWord", "expected" => "SingleWord"],
            ["input" => "   ", "expected" => null],
            ["input" => "", "expected" => null],
            ["input" => "   Leading and trailing spaces   ", "expected" => "Leading and trailing spaces"],
            ["input" => "This   has   multiple   spaces", "expected" => "This has multiple spaces"],
            ["input" => null, "expected" => null],  // Проверка на null
        ];

        foreach ($testCases as $testCase) {
            // Проверяем, если входное значение null, просто сравниваем с null
            if ($testCase['input'] === null) {
                $this->assertEquals(null, $testCase['expected']);
            } else {
                $result = CreatorQuizHelper::trimString($testCase['input']);
                $this->assertEquals($testCase['expected'], $result);
            }
        }
    }


    /**
     * Тест на поиск незначительных ошибок
     */
    public function test_CheckDataForMinorErrors(): void
    {
        $questions = [
            ["id" => 1, "question" => null, 'answers' => [null, null, null], "correctAnswerIndex" => 1],
            ["id" => 2, "question" => null, "answers" => [], "correctAnswerIndex" => 1],
            ["id" => 3, "question" => "What is your name?", "answers" => ["Alice", "Bob", null], "correctAnswerIndex" => 1],
            ["id" => 4, "question" => "What is your name?", "answers" => ["Alice", null, null, "Bob", null], "correctAnswerIndex" => 1],
        ];

        $expectedErrors = [
            [
                "id_question" => 1,
                "errors" => [
                    [
                        "id_error" => 1,
                        "text_error" => "Создана страница вопроса, но она не заполнена."
                    ],
                ]
            ],
            [
                "id_question" => 2,
                "errors" => [
                    [
                        "id_error" => 1,
                        "text_error" => "Создана страница вопроса, но она не заполнена."
                    ]
                ]
            ],
            [
                "id_question" => 3,
                "errors" => [
                    [
                        "id_error" => 2,
                        "text_error" => "Создано поле варианта ответа №3, но оно не заполнено."
                    ]
                ]
            ],
            [
                "id_question" => 4,
                "errors" => [
                    [
                        "id_error" => 2,
                        "text_error" => "Создано поле варианта ответа №3, но оно не заполнено."
                    ],
                    [
                        "id_error" => 2,
                        "text_error" => "Создано поле варианта ответа №5, но оно не заполнено."
                    ]

                ]
            ],
        ];

        $minor_errors = CreatorQuizHelper::checkDataForMinorErrors($questions);
        $this->assertEquals($expectedErrors, $minor_errors);
    }


    /**
     * Тест на фильтрацию вопросов
     */
    public function test_FilterQuestions(): void
    {
        $questions = [
            ["id" => 1, "question" => "What is your name?", "answers" => ["Alice", "Bob"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => null, "answers" => ["Alice", "Bob"], "correctAnswerIndex" => 0],
            ["id" => 3, "question" => "What is your name?", "answers" => [null, null], "correctAnswerIndex" => 0],
            ["id" => 4, "question" => "What is your favorite color?", "answers" => ["Red", null], "correctAnswerIndex" => 0],
            ["id" => 5, "question" => null, "answers" => ["Red", null, null], "correctAnswerIndex" => 0],
            ["id" => 6, "question" => null, "answers" => [null, null, null], "correctAnswerIndex" => 0],
            ["id" => 7, "question" => null, "answers" => [], "correctAnswerIndex" => 0],
        ];

        $expectedQuestions = [
            ["id" => 1, "question" => "What is your name?", "answers" => ["Alice", "Bob"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => null, "answers" => ["Alice", "Bob"], "correctAnswerIndex" => 0],
            ["id" => 3, "question" => "What is your name?", "answers" => [null, null], "correctAnswerIndex" => 0],
            ["id" => 4, "question" => "What is your favorite color?", "answers" => ["Red", null], "correctAnswerIndex" => 0],
            ["id" => 5, "question" => null, "answers" => ["Red", null, null], "correctAnswerIndex" => 0],
        ];

        $filteredQuestions = CreatorQuizHelper::filterQuestions($questions, true);

        $this->assertCount(5, $filteredQuestions);
        $this->assertEquals($expectedQuestions, $filteredQuestions);
    }


    /**
     * Тест на фильтрацию вопросов (случай, если все вопросы пустые)
     */
    public function test_FilterQuestionsWithAllEmptyQuestions(): void
    {
        $questions = [
            ["id" => 1, "question" => null, "answers" => [null, null], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => null, "answers" => [null, null], "correctAnswerIndex" => 0],
            ["id" => 3, "question" => null, "answers" => [null, null], "correctAnswerIndex" => 0]
        ];

        $expectedQuestions = [
            ["id" => 1, "question" => null, "answers" => [null, null], "correctAnswerIndex" => 0]
        ];

        $filteredQuestions = CreatorQuizHelper::filterQuestions($questions, true);

        $this->assertCount(1, $filteredQuestions);
        $this->assertEquals($expectedQuestions, $filteredQuestions);
    }


    /**
     * Тест на фильтрацию ответов в вопросах
     */
    public function test_FilterAnswers(): void
    {
        $questions = [
            ["id" => 1, "question" => "What is your favorite fruit?", "answers" => ["Apple", "Banana", "Cherry"], "correctAnswerIndex" => 2],
            ["id" => 2, "question" => "What is your favorite fruit?", "answers" => ["Apple", "Banana", "Cherry"], "correctAnswerIndex" => null],
            ["id" => 3, "question" => null, "answers" => ["Apple", "Banana", "Cherry"], "correctAnswerIndex" => 2],
            ["id" => 4, "question" => "What is your job?", "answers" => ["Engineer", "Doctor", null], "correctAnswerIndex" => 0],
            ["id" => 5, "question" => "What is your name?", "answers" => ["John", null, "Doe", null], "correctAnswerIndex" => 3],
            ["id" => 6, "question" => "What is your pet?", "answers" => [null, "Dog", "Cat", null], "correctAnswerIndex" => 3],
            ["id" => 7, "question" => "What is your pet?", "answers" => [null, "Dog", "Cat", null], "correctAnswerIndex" => 0],
            ["id" => 8, "question" => "What is your age?", "answers" => [null, null, null], "correctAnswerIndex" => 1],
            ["id" => 9, "question" => "What is your name?", "answers" => ["John", null, "Doe", null], "correctAnswerIndex" => null],
            ["id" => 10, "question" => "What is your age?", "answers" => [null, null, null], "correctAnswerIndex" => null]
        ];

        $expectedQuestions = [
            ["id" => 1, "question" => "What is your favorite fruit?", "answers" => ["Apple", "Banana", "Cherry"], "correctAnswerIndex" => 2],
            ["id" => 2, "question" => "What is your favorite fruit?", "answers" => ["Apple", "Banana", "Cherry"], "correctAnswerIndex" => null],
            ["id" => 3, "question" => null, "answers" => ["Apple", "Banana", "Cherry"], "correctAnswerIndex" => 2],
            ["id" => 4, "question" => "What is your job?", "answers" => ["Engineer", "Doctor"], "correctAnswerIndex" => 0],
            ["id" => 5, "question" => "What is your name?", "answers" => ["John", "Doe", null], "correctAnswerIndex" => 2],
            ["id" => 6, "question" => "What is your pet?", "answers" => ["Dog", "Cat", null], "correctAnswerIndex" => 2],
            ["id" => 7, "question" => "What is your pet?", "answers" => [null, "Dog", "Cat"], "correctAnswerIndex" => 0],
            ["id" => 8, "question" => "What is your age?", "answers" => [null], "correctAnswerIndex" => 0],
            ["id" => 9, "question" => "What is your name?", "answers" => ["John", "Doe"], "correctAnswerIndex" => null],
            ["id" => 10, "question" => "What is your age?", "answers" => [], "correctAnswerIndex" => null]
        ];

        $filteredQuestions = CreatorQuizHelper::filterAnswers($questions);
        $this->assertEquals($expectedQuestions, $filteredQuestions);
    }


    /**
     * Тест на поиск логических ошибок
     */
    public function test_CheckDataForLogicalErrors(): void
    {
        $questions = [
            ["id" => 1, "question" => "Какой язык программирования вы изучаете?", "answers" => ["PHP", "JavaScript", "PHP"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => "Какой язык программирования вы изучаете?", "answers" => ["JavaScript"], "correctAnswerIndex" => 0],
            ["id" => 3, "question" => "1", "answers" => ["1", "1", "1"], "correctAnswerIndex" => 0],
            ["id" => 4, "question" => "2", "answers" => ["1", "2", "3"], "correctAnswerIndex" => 0]
        ];

        $expectedErrors = [
            [
                "id_question" => 1,
                "errors" => [
                    ["id_error" => 1, "text_error" => "Встречаются одинаковые вопросы в викторине: №1, №2."],
                    ["id_error" => 2, "text_error" => "Встречаются одинаковые варианты ответов в полях: №1, №3."]
                ]
            ],
            [
                "id_question" => 2,
                "errors" => [
                    ["id_error" => 1, "text_error" => "Встречаются одинаковые вопросы в викторине: №1, №2."]
                ],
            ],
            [
                "id_question" => 3,
                "errors" => [
                    ["id_error" => 2, "text_error" => "Встречаются одинаковые варианты ответов в полях: №1, №2, №3."]
                ],
            ]

        ];

        $logic_errors = CreatorQuizHelper::checkDataForLogicalErrors($questions);
        $this->assertEquals($expectedErrors, $logic_errors);
    }


    /**
     * Тест на исправление логических ошибок
     */
    public function test_FixLogicalErrors(): void
    {
        $questions = [
            ["id" => 1, "question" => "1", "answers" => ["1", "1", "1"], "correctAnswerIndex" => 2],
            ["id" => 2, "question" => "Какой язык программирования вы изучаете?", "answers" => ["PHP", "PHP"], "correctAnswerIndex" => 1],
            ["id" => 3, "question" => "Какой язык программирования вы изучаете?", "answers" => ["JavaScript"], "correctAnswerIndex" => 0],
            ["id" => 4, "question" => "Какой язык программирования вы изучаете?1", "answers" => ["JavaScript", "JavaScript", "JavaScript"], "correctAnswerIndex" => null],
        ];

        $expectedErrors = [
            ["id" => 1, "question" => "1", "answers" => ["1"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => "Какой язык программирования вы изучаете?", "answers" => ["PHP"], "correctAnswerIndex" => 0],
            ["id" => 3, "question" => "Какой язык программирования вы изучаете?1", "answers" => ["JavaScript"], "correctAnswerIndex" => null],
        ];

        $newQuestions = CreatorQuizHelper::fixLogicalErrors($questions);

        $this->assertCount(3, $newQuestions);
        $this->assertEquals($expectedErrors, $newQuestions);
    }


    /**
     * Тест на поиск критических ошибок
     */
    public function test_CheckDataForCriticalErrors(): void
    {
        $questions = [
            ["id" => 1, "question" => null, "answers" => ["Answer 1", "Answer 2"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => "What is your name?", "answers" => ["Alice"], "correctAnswerIndex" => null],
            ["id" => 3, "question" => "What is 2 + 2?", "answers" => ["3", "4", null], "correctAnswerIndex" => 2],
            ["id" => 4, "question" => "What is 2 + 2?", "answers" => ["3", "4", null], "correctAnswerIndex" => 1]
        ];

        $expectedErrors = [
            [
                "id_question" => 1,
                "errors" => [
                    ["id_error" => 1, "text_error" => "Вопрос не должен быть null."]
                ]
            ],
            [
                "id_question" => 2,
                "errors" => [
                    ["id_error" => 2, "text_error" => "Должно быть как минимум 2 ответа."],
                    ["id_error" => 3, "text_error" => "Индекс правильного ответа не может быть null."]
                ]
            ],
            [
                "id_question" => 3,
                "errors" => [
                    ["id_error" => 4, "text_error" => "Правильный ответ не может быть null."]
                ]
            ]
        ];

        $critical_errors = CreatorQuizHelper::checkDataForCriticalErrors($questions);
        $this->assertEquals($expectedErrors, $critical_errors);
    }


    /**
     * Тест на поиск ошибок викторины
     */
    public function test_CheckNameQuizForErrors(): void
    {
        // Тест 1: Название викторины корректное
        $quizName1 = "Программирование на PHP";
        $expectedErrors1 = [];
        $name_quiz_errors1 = CreatorQuizHelper::checkNameQuizForErrors($quizName1);

        $this->assertEquals($expectedErrors1, $name_quiz_errors1);

        // Тест 2: Название викторины слишком короткое (критическая ошибка)
        $quizName2 = "PHP";
        $expectedErrors2 = ["critical_error" => "В названии викторины должно быть хотя бы 5 символов."];
        $name_quiz_errors2 = CreatorQuizHelper::checkNameQuizForErrors($quizName2);

        $this->assertEquals($expectedErrors2, $name_quiz_errors2);

        // Тест 3: Название викторины содержит лишние пробелы и перенос строки (косметические ошибки)
        $quizName3 = "  Программирование    на PHP  ";
        $expectedErrors3 = ["cosmetic_error" => "В названии викторины обнаружены лишние пробелы."];
        $name_quiz_errors3 = CreatorQuizHelper::checkNameQuizForErrors($quizName3);

        $this->assertEquals($expectedErrors3, $name_quiz_errors3);

        // Тест 4: Название викторины содержит и косметическая ошибки и критическую ошибку
        $quizName4 = "P   H";
        $expectedErrors4 = [
            "cosmetic_error" => "В названии викторины обнаружены лишние пробелы.",
            "critical_error" => "В названии викторины должно быть хотя бы 5 символов."
        ];
        $name_quiz_errors4 = CreatorQuizHelper::checkNameQuizForErrors($quizName4);

        $this->assertEquals($expectedErrors4, $name_quiz_errors4);

        // Тест 5: Название викторины пустое
        $quizName5 = "";
        $expectedErrors5 = [
            "critical_error" => "В названии викторины должно быть хотя бы 5 символов."
        ];
        $name_quiz_errors5 = CreatorQuizHelper::checkNameQuizForErrors($quizName5);

        $this->assertEquals($expectedErrors5, $name_quiz_errors5);
    }


}
