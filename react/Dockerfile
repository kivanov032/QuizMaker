# Шаг 1: Сборка приложения (builder)
FROM node:20-alpine AS builder

WORKDIR /app

# Копируем зависимости и устанавливаем их
COPY package.json package-lock.json ./
RUN npm install --frozen-lockfile

# Копируем исходный код и собираем приложение
COPY . .
RUN npm run build

# Шаг 2: Финальный образ (nginx для раздачи статики)
FROM nginx:alpine

# Копируем собранное приложение из builder-этапа
COPY --from=builder /app/dist /usr/share/nginx/html

# Копируем кастомный конфиг nginx (если требуется)
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Подставляем переменные окружения в runtime
COPY env.sh /docker-entrypoint.d/
RUN chmod +x /docker-entrypoint.d/env.sh

# Порт и команда для запуска
EXPOSE 3000
CMD ["nginx", "-g", "daemon off;"]
