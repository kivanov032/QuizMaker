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
        Schema::create('users', function (Blueprint $table) {

            $table->uuid('id_user')
                ->primary()
                ->comment('Id пользователя');

            $table->string('login', 55)
                ->unique()
                ->nullable(false)
                ->comment('Логин пользователя');

            $table->string('email', 254)
                ->unique()
                ->nullable(false)
                ->comment('Электронная почта пользователя');

            $table->string('password', 100)
                ->nullable(false)
                ->comment('Пароль пользователя к его аккаунту');

            $table->integer('created_quizzes_counter')
                ->default(0)
                ->unsigned()
                ->comment('Кол-во созданных пользователем викторин');

            $table->integer('taken_quizzes_counter')
                ->default(0)
                ->unsigned()
                ->comment('Кол-во пройденных пользователем викторин');

            $table->index('id_user', 'idx_user');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
