<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Определяет состояние модели по умолчанию.
     *
     * @return array<string, mixed>
     */
    protected $model = Quiz::class;
    public function definition(): array
    {
        return [
            'id_quiz' => Str::uuid(),
            'name_quiz' => $this->faker->sentence(3),
            'was_taken' => $this->faker->numberBetween(0, 100),
            'is_ready' => $this->faker->boolean,
            'login_user' => $this->faker->userName(),
        ];
    }
}
