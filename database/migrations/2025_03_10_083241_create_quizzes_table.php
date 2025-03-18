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
        Schema::create('quizzes', function (Blueprint $table) {

            $table->uuid('id_quiz')
                ->primary()
                ->comment('Id викторины');

            $table->string('name_quiz', 200)
                ->nullable(false)
                ->comment('Название викторины');

            $table->integer('was_taken')
                ->default(0)
                ->unsigned()
                ->comment('Кол-во раз прохождения викторины');

            $table->boolean('is_ready')
                ->default(false)
                ->comment('Метка на готовность викторины');

            $table->uuid('id_user')
                ->nullable(true)
                ->comment('Id пользователя, создавшего викторину');

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Quizzes');
    }
};
