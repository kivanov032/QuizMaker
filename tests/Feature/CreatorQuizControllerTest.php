<?php

namespace Tests\Feature;

use Tests\TestCase;

class CreatorQuizControllerTest extends TestCase
{
    public function testQuestionsReturnsEditedQuestionsAndErrors1()
    {
        // Подготовьте данные для запроса
        $data = [
            [
                "id" => 1,
                "question" => "What is 2 + 2?",
                "answers" => ["2", "4"],
                "correctAnswerIndex" => 1
            ]
        ];

        // Выполните POST-запрос к вашему маршруту
        $response = $this->json('POST', '/api/questions', $data);

        // Проверьте, что ответ имеет статус 200
        $response->assertStatus(200);

        // Проверьте структуру ответа
        $response->assertJsonStructure([
            'questionsEdited',
            'errors',
        ]);

        // Ожидаемые данные для questionsEdited
        $expectedQuestionsEdited = [
            [
                "id" => 1,
                "question" => "What is 2 + 2?",
                "answers" => ["2", "4"],
                "correctAnswerIndex" => 1
            ]
        ];

        // Ожидаемые ошибки (в данном случае пустой массив)
        $expectedErrors = [];

        // Проверяем, что в ответе есть ожидаемые данные
        $response->assertJsonFragment($expectedQuestionsEdited);
        $response->assertJsonFragment(['errors' => $expectedErrors]);
    }

    public function testQuestionsReturnsEditedQuestionsAndErrors2()
    {
        // Подготовьте данные для запроса
        $data = [
            [
                "id" => 2,
                "question" => "  \n",
                "answers" => ["2", null],
                "correctAnswerIndex" => 0
            ]
        ];

        // Выполните POST-запрос к вашему маршруту
        $response = $this->json('POST', '/api/questions', $data);

        // Проверьте, что ответ имеет статус 200
        $response->assertStatus(200);

        // Проверьте структуру ответа
        $response->assertJsonStructure([
            'questionsEdited',
            'errors',
        ]);

        // Ожидаемые данные для questionsEdited
        $expectedQuestionsEdited = [
            [
                "id" => 1,
                "question" => null,
                "answers" => ["2"],
                "correctAnswerIndex" => 0
            ]
        ];

        // Ожидаемые ошибки (в данном случае пустой массив)
        $expectedErrors = [
            [
                "id_question" => 1,
                "errors" => [
                    ["id_error" => 1, "text_error" => "Вопрос не должен быть null"],
                    ["id_error" => 2, "text_error" => "Должно быть как минимум 2 ответа"]
                ]
            ]
        ];

        // Проверяем, что в ответе есть ожидаемые данные
        $response->assertJsonFragment($expectedQuestionsEdited);
        $response->assertJsonFragment(['errors' => $expectedErrors]);
    }

}








