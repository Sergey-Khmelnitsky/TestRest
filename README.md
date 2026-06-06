# TestRest

**Тестовое задание** на Laravel 13.

[![CI](https://github.com/Sergey-Khmelnitsky/TestRest/actions/workflows/ci.yml/badge.svg)](https://github.com/Sergey-Khmelnitsky/TestRest/actions/workflows/ci.yml)

Репозиторий содержит решение двух задач: валидация HTML-постов и rate limiting комментариев с in-memory хранилищем в long-running процессе (Laravel Octane + RoadRunner).

## Задачи

### 1. Валидация постов

Функция `validatePost(string $post): bool` проверяет HTML по правилам:

- разрешённые теги: `a`, `code`, `i`, `strike`, `strong`;
- XHTML-совместимость (нижний регистр, двойные кавычки, без самозакрывающихся тегов);
- у `<a>` только атрибуты `href` и `title`;
- корректная вложенность и закрытие тегов;
- запрет «голого» символа `<` в тексте.

### 2. Rate limiting комментариев

Класс `CommentLimiter` ограничивает отправку постов:

- **3 поста за 10 секунд** на одного пользователя (sliding window);
- периодическая глобальная очистка каждые **60 секунд**;
- состояние хранится **в памяти** (`static`-свойства), без Redis и БД;
- для сохранения состояния между запросами используется **Laravel Octane**.

## Стек

- PHP 8.3+
- Laravel 13
- PostgreSQL
- Laravel Octane + RoadRunner
- GitHub Actions (CI/CD)

## Установка

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

В `.env` укажите подключение к PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=testrest
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

## Запуск

### Octane (рекомендуется для rate limiter)

```bash
composer run octane
```

Приложение: http://127.0.0.1:8000

Остановка:

```bash
composer run octane:stop
```

### Режим разработки

```bash
composer dev
```

## Тесты

```bash
composer test
```

Тесты используют PostgreSQL (настройки в `phpunit.xml`).

## CI/CD

### CI (основная проверка)

При push/PR в `main` автоматически запускается **CI**:

- PHPUnit (~138 тестов) с PostgreSQL;
- проверка стиля кода (Pint);
- сборка фронтенда.

Этого достаточно для проверки тестового задания — **сервер для деплоя не требуется**.

### Deploy (опционально)

Workflow **Deploy (optional)** — шаблон для деплоя на свой сервер. Запускается **только вручную** (Actions → Deploy (optional) → Run workflow).

Если секреты не настроены, workflow завершится успешно с пояснением — это нормально для тестового задания.

Для реального деплоя добавьте в **Settings → Secrets and variables → Actions**:

| Секрет | Описание |
|--------|----------|
| `DEPLOY_HOST` | IP или домен сервера |
| `DEPLOY_USER` | SSH-пользователь |
| `DEPLOY_SSH_KEY` | Приватный SSH-ключ |
| `DEPLOY_PATH` | Путь к проекту на сервере |
| `DEPLOY_PORT` | Необязательно, порт SSH (по умолчанию 22) |

## Для проверяющего

1. **Быстрая проверка** — откройте вкладку [Actions](https://github.com/Sergey-Khmelnitsky/TestRest/actions/workflows/ci.yml): зелёный CI = тесты проходят.
2. **Локальный запуск** — см. разделы «Установка» и «Запуск» выше. Нужны PHP 8.3+, PostgreSQL, Composer, Node.js.
3. **Rate limiter** — для проверки ограничения 3 поста / 10 сек запускайте через Octane (`composer run octane`), не через `php artisan serve`.
4. **Деплой** — не обязателен; при необходимости можно развернуть по инструкции выше на любом VPS с PHP, PostgreSQL и SSH.

## Лицензия

MIT
