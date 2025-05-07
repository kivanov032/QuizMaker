<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Support\Str;
use Tests\TestCase;

class PassingQuizControllerTest extends TestCase
{

    /**
     * --- Тестирование метода searchQuiz ---
     */

    /**
     * Тест успешного поиска викторин.
     */
    public function test_search_quiz_successful_search(): void
    {
        $quiz1 = Quiz::factory()->create(['name_quiz' => 'История Древнего мира', 'is_ready' => true]);
        $quiz2 = Quiz::factory()->create(['name_quiz' => 'История Средних веков', 'is_ready' => true]);
        $quiz3 = Quiz::factory()->create(['name_quiz' => 'География', 'is_ready' => true]);

        $response = $this->postJson('/api/search-quiz', [
            'quizName' => 'История'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'quizzes' => [
                    '*' => [
                        'id_quiz',
                        'name_quiz',
                        'was_taken',
                        'is_ready',
                        'login_user',
                        'created_at'
                    ]
                ],
                'message'
            ])
            ->assertJsonCount(2, 'quizzes');

        $quiz1->delete();
        $quiz2->delete();
        $quiz3->delete();
    }

    /**
     * Тест поиска с учетом регистра.
     */
    public function test_search_quiz_case_insensitive(): void
    {
        $quiz = Quiz::factory()->create(['name_quiz' => 'Физика для начинающих', 'is_ready' => true]);

        $response = $this->postJson('/api/search-quiz', [
            'quizName' => 'фИзИкА'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'quizzes' => [
                    '*' => [
                        'id_quiz',
                        'name_quiz',
                        'was_taken',
                        'is_ready',
                        'login_user',
                        'created_at'
                    ]
                ],
                'message'
            ])
            ->assertJsonCount(1, 'quizzes');

        $quiz->delete();
    }

    /**
     * Тест поиска несуществующей викторины.
     */
    public function test_search_quiz_not_found(): void
    {
        $quiz = Quiz::factory()->create(['name_quiz' => 'Другая тема']);

        $response = $this->postJson('/api/search-quiz', [
            'quizName' => 'Несуществующая викторина'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Викторины не найдены'
            ]);

        $quiz->delete();
    }

    /**
     * Тест поиска с ошибкой валидации.
     */
    public function test_search_quiz_validation_error(): void
    {
        $response = $this->postJson('/api/search-quiz', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quizName'])
            ->assertJson([
                'message' => 'Поле quiz name обязательно для заполнения.',
                'errors' => [
                    'quizName' => [
                        'Поле quiz name обязательно для заполнения.'
                    ]
                ]
            ]);
    }


    /**
     * --- Тестирование метода downloadQuizQuestion (обновлённая версия) ---
     */

    /**
     * Тест успешного получения вопросов викторины в обновлённом формате вывода.
     */
    public function test_get_quiz_questions_successful(): void
    {
        $quiz = Quiz::factory()->create(['is_ready' => true]);
        $question1 = QuizQuestion::factory()->create([
            'id_quiz' => $quiz->id_quiz,
            'text_question' => 'Вопрос 1',
            'correct_option' => 'Правильный ответ 1',
            'wrong_option' => ['Неправильный 1', 'Неправильный 2']
        ]);
        $question2 = QuizQuestion::factory()->create([
            'id_quiz' => $quiz->id_quiz,
            'text_question' => 'Вопрос 2',
            'correct_option' => 'Правильный ответ 2',
            'wrong_option' => ['Ошибка 1', 'Ошибка 2', 'Ошибка 3']
        ]);

        $response = $this->postJson('/api/get-quiz-questions', [
            'id_quiz' => $quiz->id_quiz
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'questions' => [
                    '*' => [
                        'id',
                        'question',
                        'answers',
                        'correctAnswerIndex'
                    ]
                ],
                'message'
            ])
            ->assertJsonCount(2, 'questions');

        $responseQuestions = $response->json('questions');
        $this->assertCount(2, $responseQuestions);

        $this->assertEqualsCanonicalizing(
            ['Вопрос 1', 'Вопрос 2'],
            [$responseQuestions[0]['question'], $responseQuestions[1]['question']]
        );

        foreach ($responseQuestions as $responseQuestion) {
            $this->assertIsInt($responseQuestion['correctAnswerIndex']);
            $this->assertGreaterThanOrEqual(0, $responseQuestion['correctAnswerIndex']);
            $this->assertLessThan(count($responseQuestion['answers']), $responseQuestion['correctAnswerIndex']);

            $correctAnswer = $responseQuestion['question'] === 'Вопрос 1'
                ? 'Правильный ответ 1'
                : 'Правильный ответ 2';
            $this->assertContains($correctAnswer, $responseQuestion['answers']);
        }

        $question1->delete();
        $question2->delete();
        $quiz->delete();
    }

    /**
     * Тест корректности индекса правильного ответа.
     */
    public function test_correct_answer_index_validity(): void
    {
        $quiz = Quiz::factory()->create(['is_ready' => true]);
        $question = QuizQuestion::factory()->create([
            'id_quiz' => $quiz->id_quiz,
            'correct_option' => 'Истинный ответ',
            'wrong_option' => ['Ложь 1', 'Ложь 2']
        ]);

        $response = $this->postJson('/api/get-quiz-questions', [
            'id_quiz' => $quiz->id_quiz
        ]);

        $responseQuestion = $response->json('questions')[0];
        $this->assertEquals('Истинный ответ', $responseQuestion['answers'][$responseQuestion['correctAnswerIndex']]);

        $question->delete();
        $quiz->delete();
    }

    /**
     * Тест попытки получения вопросов несуществующей викторины.
     */
    public function test_get_quiz_questions_not_found(): void
    {
        $nonExistentId = Str::uuid();

        $response = $this->postJson('/api/get-quiz-questions', [
            'id_quiz' => $nonExistentId
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Вопросы викторины не найдены'
            ]);
    }

    /**
     * Тест валидации запроса без id_quiz.
     */
    public function test_get_quiz_questions_validation_error(): void
    {
        $response = $this->postJson('/api/get-quiz-questions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_quiz']);
    }

    /**
     * --- Тестирование метода checkQuizAnswers ---
     */

    /**
     * Тест успешной проверки ответов викторины.
     */
    public function test_check_quiz_answers_successful(): void
    {
        $quiz = Quiz::factory()->create(['is_ready' => true]);
        $question1 = QuizQuestion::factory()->create([
            'id_quiz' => $quiz->id_quiz,
            'text_question' => 'Вопрос 1',
            'correct_option' => 'Правильный ответ 1',
            'wrong_option' => ['Неправильный 1', 'Неправильный 2']
        ]);
        $question2 = QuizQuestion::factory()->create([
            'id_quiz' => $quiz->id_quiz,
            'text_question' => 'Вопрос 2',
            'correct_option' => 'Правильный ответ 2',
            'wrong_option' => ['Ошибка 1', 'Ошибка 2']
        ]);

        $questionsResponse = $this->postJson('/api/get-quiz-questions', [
            'id_quiz' => $quiz->id_quiz
        ]);
        $quizQuestions = $questionsResponse->json('questions');

        // Исправленные ответы (учитываем реальные индексы)
        $userAnswers = [
            $quizQuestions[0]['id'] => $quizQuestions[0]['correctAnswerIndex'],
            $quizQuestions[1]['id'] => ($quizQuestions[1]['correctAnswerIndex'] + 1) % 3
        ];

        $response = $this->postJson('/api/check-quiz-answers', [
            'userAnswers' => $userAnswers,
            'quizQuestions' => $quizQuestions,
            'id_quiz' => $quiz->id_quiz
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'results',
                'message',
                'summary' => [
                    'totalQuestions',
                    'correctAnswers',
                    'wrongAnswers',
                    'skippedQuestions',
                    'scorePercentage'
                ]
            ])
            ->assertJson([
                'summary' => [
                    'totalQuestions' => 2,
                    'correctAnswers' => 1,
                    'wrongAnswers' => 1,
                    'skippedQuestions' => 0,
                    'scorePercentage' => 50,
                ]
            ]);

        $question1->delete();
        $question2->delete();
        $quiz->delete();
    }


    /**
     * Тест проверки с пропущенными вопросами.
     */
    public function test_check_quiz_answers_with_skipped_questions(): void
    {
        $quiz = Quiz::factory()->create(['is_ready' => true]);
        $question = QuizQuestion::factory()->create([
            'id_quiz' => $quiz->id_quiz,
            'text_question' => 'Вопрос',
            'correct_option' => 'Правильный ответ',
            'wrong_option' => ['Неправильный']
        ]);

        $questionsResponse = $this->postJson('/api/get-quiz-questions', [
            'id_quiz' => $quiz->id_quiz
        ]);
        $quizQuestions = $questionsResponse->json('questions');

        $response = $this->postJson('/api/check-quiz-answers', [
            'userAnswers' => [],
            'quizQuestions' => $quizQuestions,
            'id_quiz' => $quiz->id_quiz
        ]);


        $response->assertStatus(200)
            ->assertJson([
                'summary' => [
                    'totalQuestions' => 1,
                    'correctAnswers' => 0,
                    'wrongAnswers' => 0,
                    'skippedQuestions' => 1,
                    'scorePercentage' => 0.0
                ]
            ]);

        $results = $response->json('results');
        $this->assertEquals('Not Entered', $results[$quizQuestions[0]['id']]['status']);

        $question->delete();
        $quiz->delete();
    }


    /**
     * Тест валидации запроса без обязательных полей.
     */
    public function test_missing_required_fields(): void
    {
        $response = $this->postJson('/api/check-quiz-answers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'quizQuestions'
            ])
            ->assertJsonMissingValidationErrors(['userAnswers']);
    }

}
