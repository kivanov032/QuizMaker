<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\CreateQuizController;
use PHPUnit\Framework\TestCase;
use function Ramsey\Uuid\v1;

class CreateQuizControllerTest extends TestCase
{
    private CreateQuizController $controller;

    protected function setUp(): void
    {
        $this->controller = new CreateQuizController();
    }

    //Тест на 'трим' вопросов
    public function testTrimQuestions()
    {
        $questions = [
            ["question" => "   What is your name?   ", "answers" => ["John", "   ", "Doe"], "correctAnswerIndex" => 0],
            ["question" => "   ", "answers" => ["  ", "25", ""], "correctAnswerIndex" => 0],
            ["question" => "", "answers" => ["", "   ", ""], "correctAnswerIndex" => 0],
            ["question" => "How old are you?", "answers" => ["  ", "", "30"], "correctAnswerIndex" => 0],
            ["question" => "What is your favorite color?", "answers" => ["Red", "Blue", "Green"], "correctAnswerIndex" => 0],
            ["question" => "   What is your name?\n   ", "answers" => ["John\n", "   ", "Doe"], "correctAnswerIndex" => 0],
            ["question" => "\n   ", "answers" => ["\n", "25", "\r\n"], "correctAnswerIndex" => 0]
        ];

        $expected = [
            ["question" => "What is your name?", "answers" => ["John", null, "Doe"], "correctAnswerIndex" => 0],
            ["question" => null, "answers" => [null, "25", null], "correctAnswerIndex" => 0],
            ["question" => null, "answers" => [null, null, null], "correctAnswerIndex" => 0],
            ["question" => "How old are you?", "answers" => [null, null, "30"], "correctAnswerIndex" => 0],
            ["question" => "What is your favorite color?", "answers" => ["Red", "Blue", "Green"], "correctAnswerIndex" => 0],
            ["question" => "What is your name?", "answers" => ["John", null, "Doe"], "correctAnswerIndex" => 0],
            ["question" => null, "answers" => [null, "25", null], "correctAnswerIndex" => 0]
        ];

        $result = $this->controller->getTrimQuestions($questions);
        $this->assertEquals($expected, $result);
    }

// Тест на фильтрацию ответов в вопросах
    public function testFilterAnswers()
    {
        $questions = [
            ["question" => "What is your favorite fruit?", "answers" => ["Apple", "Banana", "Cherry"], "correctAnswerIndex" => 2],
            ["question" => "What is your favorite fruit?", "answers" => ["Apple", "Banana", "Cherry"], "correctAnswerIndex" => null],
            ["question" => null, "answers" => ["Apple", "Banana", "Cherry"], "correctAnswerIndex" => 2],
            ["question" => "What is your job?", "answers" => ["Engineer", "Doctor", null], "correctAnswerIndex" => 0],
            ["question" => "What is your name?", "answers" => ["John", null, "Doe", null], "correctAnswerIndex" => 3],
            ["question" => "What is your pet?", "answers" => [null, "Dog", "Cat", null], "correctAnswerIndex" => 3],
            ["question" => "What is your pet?", "answers" => [null, "Dog", "Cat", null], "correctAnswerIndex" => 0],
            ["question" => "What is your age?", "answers" => [null, null, null], "correctAnswerIndex" => 1],
            ["question" => "What is your name?", "answers" => ["John", null, "Doe", null], "correctAnswerIndex" => null],
            ["question" => "What is your age?", "answers" => [null, null, null], "correctAnswerIndex" => null]
        ];

        $filteredQuestions = $this->controller->getQuestionsWithFilteredAnswers($questions);

        $expectedQuestions = [
            ["question" => "What is your favorite fruit?", "answers" => ["Apple", "Banana", "Cherry"], "correctAnswerIndex" => 2],
            ["question" => "What is your favorite fruit?", "answers" => ["Apple", "Banana", "Cherry"], "correctAnswerIndex" => null],
            ["question" => null, "answers" => ["Apple", "Banana", "Cherry"], "correctAnswerIndex" => 2],
            ["question" => "What is your job?", "answers" => ["Engineer", "Doctor"], "correctAnswerIndex" => 0],
            ["question" => "What is your name?", "answers" => ["John", "Doe", null], "correctAnswerIndex" => 2],
            ["question" => "What is your pet?", "answers" => ["Dog", "Cat", null], "correctAnswerIndex" => 2],
            ["question" => "What is your pet?", "answers" => [null, "Dog", "Cat"], "correctAnswerIndex" => 0],
            ["question" => "What is your age?", "answers" => [null], "correctAnswerIndex" => 0],
            ["question" => "What is your name?", "answers" => ["John", "Doe"], "correctAnswerIndex" => null],
            ["question" => "What is your age?", "answers" => [], "correctAnswerIndex" => null]
        ];

        $this->assertEquals($expectedQuestions, $filteredQuestions);
    }

// Тест на фильтрацию вопросов
    public function testFilterQuestions()
    {
        $questions = [
            ["question" => "What is your name?", "answers" => ["Alice", "Bob"]],
            ["question" => null, "answers" => ["Alice", "Bob"]],
            ["question" => "What is your name?", "answers" => [null, null]],
            ["question" => "What is your favorite color?", "answers" => ["Red", null]],
            ["question" => null, "answers" => ["Red", null, null]],
            ["question" => null, "answers" => [null, null, null]],
            ["question" => null, "answers" => []],
        ];

        $expectedQuestions = [
            ["question" => "What is your name?", "answers" => ["Alice", "Bob"]],
            ["question" => null, "answers" => ["Alice", "Bob"]],
            ["question" => "What is your name?", "answers" => [null, null]],
            ["question" => "What is your favorite color?", "answers" => ["Red", null]],
            ["question" => null, "answers" => ["Red", null, null]],
        ];

        $filteredQuestions = $this->controller->getFilteredQuestions($questions);

        $this->assertCount(5, $filteredQuestions);
        $this->assertEquals($expectedQuestions, $filteredQuestions);
    }

// Тест на редактор вопросов
    public function testGetQuestionsAfterQuizDataEditor()
    {
        $questions = [
            ["id" => 1, "question" => "What is your name?", "answers" => ["Alice", "Bob"], "correctAnswerIndex" => 1],
            ["id" => 2, "question" => "    \t  \n", "answers" => [null, "     \n"], "correctAnswerIndex" => 0],
            ["question" => "What is your name?", "answers" => ["Alice", null], "correctAnswerIndex" => 0],
        ];

        $expectedQuestions = [
            ["id" => 1, "question" => "What is your name?", "answers" => ["Alice", "Bob"], "correctAnswerIndex" => 1],
            ["id" => 2, "question" => "What is your name?", "answers" => ["Alice"], "correctAnswerIndex" => 0]
        ];

        $filteredQuestions = $this->controller->getQuestionsAfterQuizDataEditor($questions);

        $this->assertCount(2, $filteredQuestions);
        $this->assertEquals($expectedQuestions, $filteredQuestions);
    }
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//Добавить тесты!!!!!

// Тест на валидацию вопросов (с ошибками)
    public function testValidateQuestionsWithErrors()
    {
        $questions = [
            [
                "id" => 1,
                "question" => null,
                "answers" => ["Answer 1", "Answer 2"],
                "correctAnswerIndex" => 0
            ],
            [
                "id" => 2,
                "question" => "What is your name?",
                "answers" => ["Alice"],
                "correctAnswerIndex" => null
            ],
            [
                "id" => 3,
                "question" => "What is 2 + 2?",
                "answers" => ["3", "4", null],
                "correctAnswerIndex" => 2
            ]
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

        $errors = $this->controller->getValidateQuestions($questions);

        $this->assertEquals($expectedErrors, $errors);
    }

// Тест на валидацию вопросов (без ошибок)
    public function testValidateQuestionsWithoutErrors()
    {
        $questions = [
            [
                "id" => 1,
                "question" => "Question 1",
                "answers" => ["Answer 1", "Answer 2"],
                "correctAnswerIndex" => 0
            ]
        ];

        $expectedErrors = [];

        $errors = $this->controller->getValidateQuestions($questions);

        $this->assertEquals($expectedErrors, $errors);
    }


}

