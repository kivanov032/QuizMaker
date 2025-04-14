<?php declare(strict_types=1);

return [
    /*
     | Your kafka brokers url.
     */
    'brokers' => env('KAFKA_BROKERS', 'localhost:9092'), // Исправлено: явное указание localhost

    /*
     | Default security protocol
     */
    'securityProtocol' => env('KAFKA_SECURITY_PROTOCOL', 'PLAINTEXT'),

    /*
     | Default sasl configuration
     */
    'sasl' => [
        'mechanisms' => env('KAFKA_MECHANISMS', 'PLAIN'),
        'username' => env('KAFKA_USERNAME', ''),
        'password' => env('KAFKA_PASSWORD', '')
    ],

    /*
     | Kafka consumers configuration
     */
    'consumer_group_id' => env('KAFKA_CONSUMER_GROUP_ID', 'laravel_group'), // Уникальный ID группы
    'consumer_timeout_ms' => env("KAFKA_CONSUMER_DEFAULT_TIMEOUT", 5000), // Увеличен таймаут
    'offset_reset' => env('KAFKA_OFFSET_RESET', 'earliest'), // Чтение с начала топика
    'auto_commit' => env('KAFKA_AUTO_COMMIT', false), // Ручное управление коммитами
    'sleep_on_error' => env('KAFKA_ERROR_SLEEP', 3), // Оптимизированное время повтора

    /*
     | Producer settings
     */
    'partition' => env('KAFKA_PARTITION', -1),
    'compression' => env('KAFKA_COMPRESSION_TYPE', 'lz4'), // Более эффективное сжатие
    'debug' => env('KAFKA_DEBUG', true), // Включены логи для диагностики

    /*
     | Performance tuning
     */
    'batch_repository' => env('KAFKA_BATCH_REPOSITORY', \Junges\Kafka\BatchRepositories\InMemoryBatchRepository::class),
    'flush_retry_sleep_in_ms' => 200, // Увеличен интервал повтора
    'flush_retries' => 15, // Больше попыток флаша
    'flush_timeout_in_ms' => 5000, // Увеличен таймаут флаша

    /*
     | Cache and message settings
     */
    'cache_driver' => env('KAFKA_CACHE_DRIVER', env('CACHE_DRIVER', 'file')),
    'message_id_key' => env('MESSAGE_ID_KEY', 'x-request-id'),

    /*
     | Topics mapping
     */
    'topics' => [
        'quiz_created' => env('KAFKA_TOPIC_QUIZ_CREATED', 'quiz_created'), // Упрощенное имя топика
    ],

    /*
     | Advanced producer settings (добавлено)
     */
    'producer' => [
        'message.timeout.ms' => 30000,
        'socket.timeout.ms' => 20000,
        'enable.idempotence' => true,
        'queue.buffering.max.ms' => 100
    ],

    /*
     | Advanced consumer settings (добавлено)
     */
    'consumer' => [
        'max.poll.interval.ms' => 300000,
        'heartbeat.interval.ms' => 3000
    ]
];
