<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\CreatorQuizController;
//use App\Models\Quiz;
//use App\Models\QuizQuestion;
//use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;;


class CreatorQuizControllerTest extends TestCase
{
    private CreatorQuizController $controller;

    protected function setUp(): void
    {
        $this->controller = new CreatorQuizController();
    }


//// Тест на редактор вопросов
//    public function testGetQuestionsAfterQuizDataEditor()
//    {
//        $questions = [
//            ["id" => 1, "question" => "What is your name?", "answers" => ["Alice", "Bob"], "correctAnswerIndex" => 1],
//            ["id" => 2, "question" => "    \t  \n", "answers" => [null, "     \n"], "correctAnswerIndex" => 0],
//            ["question" => "What is your name?", "answers" => ["Alice", null], "correctAnswerIndex" => 0],
//        ];
//
//        $expectedQuestions = [
//            ["id" => 1, "question" => "What is your name?", "answers" => ["Alice", "Bob"], "correctAnswerIndex" => 1],
//            ["id" => 2, "question" => "What is your name?", "answers" => ["Alice"], "correctAnswerIndex" => 0]
//        ];
//
//        $filteredQuestions = $this->controller->getQuestionsAfterQuizDataEditor($questions);
//
//        $this->assertCount(2, $filteredQuestions);
//        $this->assertEquals($expectedQuestions, $filteredQuestions);
//    }



    // Тест на функцию, которая обращает фактически пустые поля в null
    public function testCleanEmptyFields()
    {
        $questions = [
            ["id" => 1, "question" => "   \n\t", 'answers' => ["John", "   ", "\t\n", "Doe"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => "What is your name?", "answers" => ["Alice", "Bob", "   ", "\n"], "correctAnswerIndex" => 1]
        ];

        $expectedResult = [
            ["id" => 1, "question" => null, "answers" => ["John", null, null, "Doe"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => "What is your name?", "answers" => ["Alice", "Bob", null, null], "correctAnswerIndex" => 1]
        ];

        $result = $this->controller->getCleanEmptyFields($questions);
        $this->assertEquals($expectedResult, $result);
    }

    // Тест на поиск косметических ошибок
    public function testCheckDataForCosmeticErrors()
    {
        $questions = [
            ["id" => 1, "question" => "1   2", "answers" => ["3", "2   4", "4"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => "What is your name?", "answers" => ["John ", "Dave  ", "Doe   "], "correctAnswerIndex" => 0],
            ["id" => 3, "question" => "  What  is your name?  ", "answers" => ["John Grey", "John   Grey", "Doe  "], "correctAnswerIndex" => 0],
            ["id" => 4, "question" => "What  is your name?", "answers" => ["John", null, "Doe  "], "correctAnswerIndex" => 0],
        ];

        $expectedErrors = [
            [
                "id_question" => 1,
                "errors" => [
                    [
                        "id_error" => 1,
                        "text_error" => "В вопросе викторины обнаружены лишние пробелы."
                    ],
                    [
                        "id_error" => 2,
                        "text_error" => "В поле варианта ответа №2 обнаружены лишние пробелы."
                    ]
                ]
            ],
            [
                "id_question" => 2,
                "errors" => [
                    [
                        "id_error" => 2,
                        "text_error" => "В поле варианта ответа №2 обнаружены лишние пробелы."
                    ],
                    [
                        "id_error" => 2,
                        "text_error" => "В поле варианта ответа №3 обнаружены лишние пробелы."
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
                        "text_error" => "В поле варианта ответа №2 обнаружены лишние пробелы."
                    ],
                    [
                        "id_error" => 2,
                        "text_error" => "В поле варианта ответа №3 обнаружены лишние пробелы."
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
                        "text_error" => "В поле варианта ответа №3 обнаружены лишние пробелы."
                    ]

                ]
            ],
        ];

        $cosmetic_errors = $this->controller->getCheckDataForCosmeticErrors($questions);

        $this->assertEquals($expectedErrors, $cosmetic_errors);
    }

    // Тест на поиск косметических ошибок
    public function testCheckDataForCosmeticErrors1()
    {
        $questions = [
            ["id" => 1, "question" => "1   2", "answers" => ["3", "2   4", "3", null], "correctAnswerIndex" => 2]
        ];

        $expectedErrors = [
            [
                "id_question" => 1,
                "errors" => [
                    [
                        "id_error" => 1,
                        "text_error" => "В вопросе викторины обнаружены лишние пробелы."
                    ],
                    [
                        "id_error" => 2,
                        "text_error" => "В поле варианта ответа №2 обнаружены лишние пробелы."
                    ]
                ]
            ]
        ];

        $cosmetic_errors = $this->controller->getCheckDataForCosmeticErrors($questions);

        $this->assertEquals($expectedErrors, $cosmetic_errors);
    }

    //Тест на 'трим' вопросов
    public function testTrimQuestions()
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

        $result = $this->controller->getTrimQuestions($questions);

        $this->assertEquals($expected, $result);
    }

    // Тест на поиск незначительных ошибок
    public function testCheckDataForMinorErrors()
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

        $minor_errors = $this->controller->getCheckDataForMinorErrors($questions);
        $this->assertEquals($expectedErrors, $minor_errors);
    }

    // Тест на фильтрацию вопросов
    public function testFilterQuestions()
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

        $filteredQuestions = $this->controller->getFilteredQuestions($questions, true);

        $this->assertCount(5, $filteredQuestions);
        $this->assertEquals($expectedQuestions, $filteredQuestions);
    }

    // Тест на фильтрацию вопросов (случай, если все вопросы пустые)
    public function testFilterQuestionsWithAllEmptyQuestions()
    {
        $questions = [
            ["id" => 1, "question" => null, "answers" => [null, null], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => null, "answers" => [null, null], "correctAnswerIndex" => 0],
            ["id" => 3, "question" => null, "answers" => [null, null], "correctAnswerIndex" => 0]
        ];

        $expectedQuestions = [
            ["id" => 1, "question" => null, "answers" => [null, null], "correctAnswerIndex" => 0]
        ];

        $filteredQuestions = $this->controller->getFilteredQuestions($questions, true);

        $this->assertCount(1, $filteredQuestions);
        $this->assertEquals($expectedQuestions, $filteredQuestions);
    }

    public function testFilterAnswers1()
    {
        $questions = [
            ["id"=>1, "question"=>"1","answers" => [null,null,null,null],"correctAnswerIndex" => null]
        ];

        $filteredQuestions = $this->controller->getQuestionsWithFilteredAnswers($questions);

        $expectedQuestions = [
            ["id"=>1, "question"=>"1","answers" => [],"correctAnswerIndex" => null]
        ];

        $this->assertEquals($expectedQuestions, $filteredQuestions);

    }

    // Тест на фильтрацию ответов в вопросах
    public function testFilterAnswers()
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

        $filteredQuestions = $this->controller->getQuestionsWithFilteredAnswers($questions);

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

        $this->assertEquals($expectedQuestions, $filteredQuestions);
    }

    // Тест на поиск критических ошибок
    public function testCheckDataForCriticalErrors()
    {
        $questions = [
            ["id" => 1, "question" => null, "answers" => ["Answer 1", "Answer 2"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => "What is your name?", "answers" => ["Alice"], "correctAnswerIndex" => null],
            ["id" => 3, "question" => "What is 2 + 2?", "answers" => ["3", "4", null], "correctAnswerIndex" => 2]
        ];

        $expectedErrors = [
            [
                "id_question" => 1,
                "errors" => [
                    ["id_error" => 1, "text_error" => "Вопрос не должен быть null"]
                ]
            ],
            [
                "id_question" => 2,
                "errors" => [
                    ["id_error" => 2, "text_error" => "Должно быть как минимум 2 ответа"],
                    ["id_error" => 3, "text_error" => "Индекс правильного ответа не может быть null"]
                ]
            ],
            [
                "id_question" => 3,
                "errors" => [
                    ["id_error" => 4, "text_error" => "Правильный ответ не может быть null"]
                ]
            ]
        ];

        $critical_errors = $this->controller->getCheckDataForCriticalErrors($questions);

        $this->assertEquals($expectedErrors, $critical_errors);
    }

    // Тест на поиск логических ошибок
    public function testCheckDataForLogicalErrors()
    {
        $questions = [
            ["id" => 1, "question" => "Какой язык программирования вы изучаете?", "answers" => ["PHP", "PHP"], "correctAnswerIndex" => 0],
            ["id" => 2, "question" => "Какой язык программирования вы изучаете?", "answers" => ["JavaScript"], "correctAnswerIndex" => 0],
            ["id" => 3, "question" => "1", "answers" => ["1", "1", "1"], "correctAnswerIndex" => 0],
        ];

        $expectedErrors = [
            [
                "id_question" => 1,
                "errors" => [
                    ["id_error" => 1, "text_error" => "Встречаются одинаковые вопросы в викторине: №1, №2."],
                    ["id_error" => 2, "text_error" => "Встречаются одинаковые варианты ответов в полях: №1, №2."]
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

        $logic_errors = $this->controller->getCheckDataForLogicalErrors($questions);

        $this->assertEquals($expectedErrors, $logic_errors);
    }

    public function testCheckNameQuizForErrors()
    {
        // Тест 1: Название викторины корректное
        $quizName1 = "Программирование на PHP";
        $expectedErrors1 = [];
        $name_quiz_errors1 = $this->controller->getCheckNameQuizForErrors($quizName1);

        $this->assertEquals($expectedErrors1, $name_quiz_errors1);

        // Тест 2: Название викторины слишком короткое (критическая ошибка)
        $quizName2 = "PHP";
        $expectedErrors2 = ["critical_error" => "В названии викторины должно быть хотя бы 5 символов."];
        $name_quiz_errors2 = $this->controller->getCheckNameQuizForErrors($quizName2);

        $this->assertEquals($expectedErrors2, $name_quiz_errors2);

        // Тест 3: Название викторины содержит лишние пробелы и перенос строки (косметические ошибки)
        $quizName3 = "  Программирование    на PHP  ";
        $expectedErrors3 = ["cosmetic_error" => "В названии викторины обнаружены лишние пробелы."];
        $name_quiz_errors3 = $this->controller->getCheckNameQuizForErrors($quizName3);

        $this->assertEquals($expectedErrors3, $name_quiz_errors3);

        // Тест 4: Название викторины содержит и косметическая ошибки и критическую ошибку
        $quizName4 = "P   H";
        $expectedErrors4 = [
            "cosmetic_error" => "В названии викторины обнаружены лишние пробелы.",
            "critical_error" => "В названии викторины должно быть хотя бы 5 символов."
        ];
        $name_quiz_errors4 = $this->controller->getCheckNameQuizForErrors($quizName4);

        $this->assertEquals($expectedErrors4, $name_quiz_errors4);

        // Тест 5: Название викторины пустое
        $quizName5 = "";
        $expectedErrors5 = [
            "critical_error" => "В названии викторины должно быть хотя бы 5 символов."
        ];
        $name_quiz_errors5 = $this->controller->getCheckNameQuizForErrors($quizName5);

        $this->assertEquals($expectedErrors5, $name_quiz_errors5);
    }

    // Тест на исправление логических ошибок
    public function testFixLogicalErrors()
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

        $newQuestions = $this->controller->getFixLogicalErrors($questions);

        $this->assertCount(3, $newQuestions);
        $this->assertEquals($expectedErrors, $newQuestions);
    }

    public function test1(): void
    {
        // Генерация UUID
        $uuid = Uuid::uuid4()->toString();

        // Проверка формата UUID
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid
        );

    }

//    public function testCreateQuizWithQuestions()
//    {
//        // Подготовка данных
//        $uuid = Uuid::uuid4()->toString();
//        $quizName = 'Тестовая викторина';
//        $questions = [
//            [
//                'id' => 1,
//                'question' => 'Вопрос 1',
//                'answers' => ['Ответ 1', 'Ответ 2'],
//                'correctAnswerIndex' => 1,
//            ],
//            [
//                'id' => 2,
//                'question' => 'Вопрос 2',
//                'answers' => ['Ответ 3', 'Ответ 4', 'Ответ 5'],
//                'correctAnswerIndex' => 0,
//            ],
//        ];
//
//        // Логируем информацию перед тестом
//        //Log::shouldReceive('info')->times(3);
//
//        // Выполнение транзакции
////        DB::transaction(function () use ($uuid, $quizName, $questions) {
////
////        });
//
//        $quiz = Quiz::create([
//            'id_quiz' => $uuid,
//            'name_quiz' => $quizName,
//            'is_ready' => true,
//            'id_user' => null,
//        ]);
//
//        foreach ($questions as $question) {
//            $uuidForAnswers = Uuid::uuid4()->toString();
//
//            QuizQuestion::create([
//                'id_quiz_question_answers' => $uuidForAnswers,
//                'text_question' => $question['question'],
//                'correct_option' => $question['answers'][$question['correctAnswerIndex']],
//                'wrong_option' => json_encode(array_values(array_filter($question['answers'], function ($answer) use ($question) {
//                    return $answer !== $question['answers'][$question['correctAnswerIndex']];
//                }))),
//                'id_quiz' => $quiz->id_quiz,
//            ]);
//        }
//
//
//    }



}

