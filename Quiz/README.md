# Quiz

## 1. Назначение микросервиса

**Quiz** — микросервис системы *Quiz Maker*, отвечающий за:

- создание и хранение викторин и связанных с ними вопросов;
- проверка на ошибки викторины перед её созданием;
- предоставление данных для прохождения викторины;
- приём и сохранение результатов прохождения.


## 2. Архитектура и зависимости

### Используемые технологии:

- **Язык:** PHP 8.4.4
- **Фреймворк:** Laravel 12.1.1
- **База данных:** PostgreSQL
- **Документация:** Swagger (OpenAPI)
- **Месседж-брокер:** Kafka (только отправка)
- - **Очереди:** Redis

### Взаимодействие с другими микросервисами:

!!! TODO !!!

## 3. Способы запуска сервиса

### Локальный запуск:

```bash
php artisan serve --port=8001
```

### Переменные окружения (`.env`):

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=Users_QM
DB_USERNAME=
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mail.ru
MAIL_PORT=465
MAIL_USERNAME=quizmakeroriginal@mail.ru
MAIL_PASSWORD=
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=quizmakeroriginal@mail.ru
MAIL_FROM_NAME="Quiz Maker"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

KAFKA_BROKERS=localhost:9092
KAFKA_TOPIC_QUIZ_CREATED=quiz_created
KAFKA_TOPIC=quiz_created
KAFKA_GROUP_ID=laravel-group
KAFKA_CONSUMER_GROUP_ID=quiz_created
KAFKA_COMPRESSION_CODEC=gzip
KAFKA_COMPRESSION_TYPE=gzip
KAFKA_SECURITY_PROTOCOL=PLAINTEXT
KAFKA_DEBUG=false
```

## 4. API документация

### Swagger

Документация доступна по адресу:

```
http://localhost:8001/api/documentation
```

### Основные эндпоинты:

| Метод | URL                        | Описание                                               |
|-------|-----------------------------|--------------------------------------------------------|
| GET   | `/check-activity`          | Проверка соединения с базой данных                    |
| POST  | `/search-quiz-errors`      | Поиск ошибок в викторине перед созданием             |
| POST  | `/fix-quiz-errors`         | Автоматическое исправление ошибок в викторине         |
| POST  | `/create-quiz`             | Создание новой викторины                              |
| POST  | `/search-quiz`             | Поиск викторины по коду или ID                        |
| POST  | `/get-quiz-questions`      | Получение вопросов для прохождения викторины         |
| POST  | `/check-quiz-answers`      | Проверка ответов и подсчёт результатов прохождения    |

## 5. Как тестировать

Для запуска тестов:

```bash
php artisan test
```

или

```bash
./vendor/bin/phpunit
```

## 6. Контакты и поддержка

- **Разработчики:** Иванов Константин, Астанаев Марк, Горнушкин Дмитрий
- **GitHub:** https://github.com/kivanov032, https://github.com/markast555, https://github.com/Dimaer35
- **Telegram:** https://t.me/konstantin_beast, https://t.me/Mark_8_8, https://t.me/Dimaer198
