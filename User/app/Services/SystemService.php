<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PDOException;
use Exception;

class SystemService
{
    public function checkActivity(): array
    {
        $serverStatus = 'Активен';

        try {
            DB::connection()->getPdo();
            return [
                'status' => 'success',
                'message' => 'Сервер активен, соединение с БД успешно установлено.',
                'server_status' => $serverStatus,
                'database_status' => 'Подключение к БД успешно',
            ];
        } catch (\PDOException $e) {
            return [
                'status' => 'error',
                'code' => 'DB_CONNECTION_ERROR',
                'message' => 'Ошибка подключения к БД.',
                'server_status' => $serverStatus,
                'database_status' => 'Ошибка подключения к БД',
                'error' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'code' => 'UNKNOWN_ERROR',
                'message' => 'Неизвестная ошибка при подключении к БД.',
                'server_status' => $serverStatus,
                'database_status' => 'Неизвестная ошибка',
                'error' => $e->getMessage(),
            ];
        }
    }
}
