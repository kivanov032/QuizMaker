<?php

namespace Database\Factories;

use App\Models\CodeConfirmation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CodeConfirmation>
 */
class CodeConfirmationFactory extends Factory
{
    /**
     * Определяет состояние модели по умолчанию.
     *
     * @return array<string, mixed>
     */
    protected $model = CodeConfirmation::class;
    public function definition(): array
    {
        return [
            'id_code_confirmation' => Str::uuid(),
            'email' => $this->faker->unique()->safeEmail,
            'code_confirmation' => $this->faker->numerify('######'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}



