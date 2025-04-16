<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quiz_question_answers', function (Blueprint $table) {
            // Удаляем уникальное ограничение
            $table->dropUnique(['id_quiz']);

            // Добавляем обычный индекс для ускорения поиска
            $table->index('id_quiz');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_question_answers', function (Blueprint $table) {
            // Откат изменений: удаляем индекс и восстанавливаем уникальность
            $table->dropIndex(['id_quiz']);
            $table->unique('id_quiz');
        });
    }
};
