<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropUnique(['id_user']);

            $table->dropColumn('id_user');

            $table->string('login_user', 55)
                ->nullable(true)
                ->comment('Логин пользователя, создавшего викторину')
                ->after('is_ready');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            // Откат изменений
            $table->dropColumn('login_user');

            $table->uuid('id_user')
                ->nullable(true)
                ->comment('Id пользователя, создавшего викторину')
                ->after('is_ready');

            $table->unique('id_user');
        });
    }
};
