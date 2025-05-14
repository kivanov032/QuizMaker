<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Junges\Kafka\Facades\Kafka;

class KafkaService
{
    /**
     * Отправляет сообщение в Kafka с явным указанием брокера и топика
     *
     * @param string $broker  Адрес брокера
     * @param string $topic   Название топика
     * @param array  $body    Тело сообщения
     * @param array  $config  Доп. настройки Kafka
     * @return bool
     */
    public static function publish(string $broker, string $topic, array $body, array $config = []): bool
    {
        $defaultConfig = [
            'queue.buffering.max.ms' => 300,
            'enable.idempotence' => 'true',
        ];

        try {
            Kafka::publish($broker)
                ->onTopic($topic)
                ->withBody($body)
                ->withConfigOptions(array_merge($defaultConfig, $config))
                ->send();

            return true;
        } catch (\Exception $e) {
            Log::error('Kafka publish failed', [
                'broker' => $broker,
                'topic' => $topic,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
