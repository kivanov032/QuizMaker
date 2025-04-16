<?php

namespace Database\Factories;

use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<QuizQuestion>
 */
class QuizQuestionFactory extends Factory
{
    /**
     * Определяет состояние модели по умолчанию.
     *
     * @return array<string, mixed>
     */
    protected $model = QuizQuestion::class;

    public function definition(): array
    {
        return [
            'id_quiz_question_answers' => Str::uuid(),
            'text_question' => $this->faker->sentence(10),
            'correct_option' => $this->faker->sentence(5),
            'wrong_option' => [
                $this->faker->sentence(3),
                $this->faker->sentence(4),
                $this->faker->sentence(5)
            ],
            'id_quiz' => Str::uuid(), // Или связь через фабрику Quiz::factory()
        ];
    }
}
