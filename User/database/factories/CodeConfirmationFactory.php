<?php

namespace Database\Factories;

use App\Models\CodeConfirmation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CodeConfirmationFactory extends Factory
{
    protected $model = CodeConfirmation::class;

    public function definition(): array
    {
        return [
            'id_code_confirmation' => Str::uuid(), // Генерация UUID
            'email' => $this->faker->unique()->safeEmail, // Генерация уникального email
            'code_confirmation' => $this->faker->numerify('######'), // Генерация 6-значного кода
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}



