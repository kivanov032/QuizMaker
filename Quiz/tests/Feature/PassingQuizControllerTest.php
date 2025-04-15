<?php

namespace Tests\Feature;

use App\Models\Quiz;
use Illuminate\Support\Facades\DB;
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
            'name_quiz' => 'История'
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
            'name_quiz' => 'фИзИкА'
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
            'name_quiz' => 'Несуществующая викторина'
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
            ->assertJsonValidationErrors(['name_quiz'])
            ->assertJson([
                'message' => 'Поле name quiz обязательно для заполнения.',
                'errors' => [
                    'name_quiz' => [
                        'Поле name quiz обязательно для заполнения.'
                    ]
                ]
            ]);
    }
}
