<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Очистка старых логов
        $schedule->command('logcleaner:run', [
            '--keeplines' => 5000,
            '--keepfiles' => 14,
        ])
            ->daily()
            ->at('05:00')
            ->onSuccess(function () {
                Log::info('Очистка логов выполнена успешно.');
            })
            ->onFailure(function () {
                Log::error('Очистка логов завершилась ошибкой.');
            });
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}

