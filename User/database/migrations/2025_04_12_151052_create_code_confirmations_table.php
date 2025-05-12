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
        Schema::create('code_confirmations', function (Blueprint $table) {
            $table->uuid('id_code_confirmation')
                ->primary()
                ->comment('Id пользователя');

            $table->string('email', 254)
                ->unique()
                ->comment('Электронная почта пользователя');

            $table->string('code_confirmation')
                ->nullable(false)
                ->comment('Код подтверждения');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_confirmations');
    }
};
