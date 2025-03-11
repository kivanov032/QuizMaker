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
        Schema::create('quiz_question_answers', function (Blueprint $table) {

            $table->uuid('id_quiz_question_answers')
                ->primary()
                ->comment('Id вопроса');

            $table->string('text_question', 350)
                ->nullable(false)
                ->comment('Текст вопроса');

            $table->string('correct_option', 150)
                ->nullable(false)
                ->comment('Текст правильного вопроса');

            $table->json('wrong_option')
                ->nullable(false)
                ->comment('Массив неправильных ответов');

            $table->uuid('id_quiz')
                ->nullable(false)
                ->comment('Id викторины данного вопроса');

            $table->foreign('id_quiz')
                ->references('id_quiz')
                ->on('quizzes')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_question_answers');
    }
};
