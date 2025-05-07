<?php

namespace Tests\Feature;

use App\Helpers\MailHelper;
use App\Models\CodeConfirmation;
use App\Mail\CodeConfirmationMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailSenderControllerTest extends TestCase
{
    /**
     *  --- Тестирование метода sendMailForCodeConfirmation ---
     */

    /**
     * Тест успешной отправки письма с кодом подтверждения.
     */
    public function test_send_mail_for_code_confirmation_success(): void
    {
        Mail::fake();
        $email = 'kivanov032@gmail.com';

        // Отправляем запрос на отправку письма
        $response = $this->postJson('/api/send-mail-for-code-confirmation', [
            'email' => $email,
        ]);

        // Проверяем статус ответа и сообщение
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Сообщение успешно доставлено пользователю.',
            ]);

        // Проверяем, что письмо было отправлено на указанный email
        Mail::assertSent(CodeConfirmationMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });

        // Проверяем, что запись была создана в базе данных
        $this->assertDatabaseHas('code_confirmations', [
            'email' => $email,
        ]);
    }

    /**
     * Тест отправки письма с невалидным email.
     */
    public function test_send_mail_for_code_confirmation_invalid_email(): void
    {
        Mail::fake();
        $email = 'invalid-email';

        // Отправляем запрос с невалидным email
        $response = $this->postJson('/api/send-mail-for-code-confirmation', [
            'email' => $email,
        ]);

        // Проверяем статус ответа и наличие ошибки валидации
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Проверяем, что письмо не было отправлено
        Mail::assertNotSent(CodeConfirmationMail::class);
    }

    /**
     * Тест ошибки при отправке письма.
     */
    public function test_send_mail_for_code_confirmation_mail_failure(): void
    {
        Mail::fake();
        // Имитируем ошибку при отправке письма
        Mail::shouldReceive('send')->andThrow(new \Exception('Ошибка отправки письма.'));
        $email = 'user@example.com';

        // Отправляем запрос на отправку письма
        $response = $this->postJson('/api/send-mail-for-code-confirmation', [
            'email' => $email,
        ]);

        // Проверяем статус ответа и сообщение об ошибке
        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'message' => 'Ошибка отправки письма.',
            ]);
    }

    /**
     *  --- Тестирование метода confirmCode ---
     */

    /**
     * Тест успешного подтверждения кода.
     */
    public function test_valid_code_confirmation(): void
    {
        // Генерируем код и создаем запись через фабрику
        $code = MailHelper::generateCodeConfirmation();
        $record = CodeConfirmation::factory()->create([
            'code_confirmation' => $code,
            'updated_at' => now(),
        ]);

        // Отправляем запрос с валидным кодом
        $response = $this->postJson('/api/confirm-code', [
            'input_code' => $code,
        ]);

        // Проверяем статус ответа и сообщение
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Код подтверждения верен.',
            ]);

        // Удаляем запись после теста
        $record->delete();
    }

    /**
     * Тест неверного кода подтверждения.
     */
    public function test_invalid_code_confirmation(): void
    {
        // Создаем запись с кодом через фабрику
        $record = CodeConfirmation::factory()->create([
            'code_confirmation' => '123456',
            'updated_at' => Carbon::now(),
        ]);

        // Отправляем запрос с неверным кодом
        $response = $this->postJson('/api/confirm-code', [
            'input_code' => '654321',
        ]);

        // Проверяем статус ответа и сообщение
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Неверный код подтверждения.',
            ]);

        // Удаляем запись после теста
        $record->delete();
    }

    /**
     * Тест истечения времени ожидания.
     */
    public function test_expired_code_confirmation(): void
    {
        // Создаем запись с кодом, время которой истекло
        $record = CodeConfirmation::factory()->create([
            'code_confirmation' => '123456',
            'updated_at' => Carbon::now()->subMinutes(3),
        ]);

        // Отправляем запрос с кодом
        $response = $this->postJson('/api/confirm-code', [
            'input_code' => '123456',
        ]);

        // Проверяем статус ответа и сообщение
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Время ожидания закончилось. Пожалуйста, запросите новый код.',
            ]);

        // Удаляем запись после теста
        $record->delete();
    }
}

