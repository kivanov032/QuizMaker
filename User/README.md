# User

## 1. Назначение микросервиса

**User** — микросервис системы *Quiz Maker*, отвечающий за:

- регистрацию и аутентификацию пользователей;
- отправку и проверку кода подтверждения по email;
- управление токенами доступа (Laravel Sanctum).

## 2. Архитектура и зависимости

### Используемые технологии:

- **Язык:** PHP 8.4.4
- **Фреймворк:** Laravel 12.1.1
- **База данных:** PostgreSQL
- **Аутентификация:** Laravel Sanctum
- **Почта:** SMTP (через `mail.ru`)
- **Документация:** Swagger (OpenAPI)
- **Месседж-брокер:** Kafka (только получение)

### Взаимодействие с другими микросервисами:

- **react** — взаимодействует напрямую по HTTP с использованием REST API.  
  Поддерживаемые действия:
    - регистрация (`/api/signup`),
    - проверка данных (`/api/validate-signup`),
    - вход (`/api/login`),
    - выход (`/api/logout`),
    - получение информации о текущем пользователе (`/api/user`).

- **Quiz** — взаимодействие через **Kafka**.  
  Подписка на событие `quiz_created`, при получении которого увеличивается счётчик `created_quizzes_counter` у соответствующего пользователя.

## 3. Способы запуска сервиса

### Локальный запуск:

```bash
php artisan serve --port=8000
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
http://localhost:8000/api/documentation
```

### Основные эндпоинты:

| Метод | URL                                             | Описание                                               |
|-------|--------------------------------------------------|--------------------------------------------------------|
| GET   | `/check-activity`                               | Проверка соединения с базой данных                    |
| POST  | `/validate-signup`                              | Проверка данных регистрации                           |
| POST  | `/signup`                                       | Регистрация пользователя                              |
| POST  | `/login`                                        | Авторизация пользователя                              |
| POST  | `/logout`                                       | Выход (удаление токена) *(требует авторизации)*       |
| GET   | `/user`                                         | Получение текущего пользователя *(требует авторизации)*|
| POST  | `/increment-created-quizzes-counter`           | Инкремент счётчика викторин пользователя              |
| POST  | `/send-mail-for-code-confirmation`             | Отправка email-кода подтверждения                     |
| POST  | `/confirm-code`                                 | Подтверждение email-кода                              |

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
