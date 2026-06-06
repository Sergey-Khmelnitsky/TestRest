# TestRest

**Тестовое задание** на Laravel 13.

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

- **CI** — при push/PR в `main`: PHPUnit, Pint, сборка фронтенда.
- **Deploy** — автоматически после успешного CI на `main`, также доступен ручной запуск.

Для деплоя нужны секреты: `DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_SSH_KEY`, `DEPLOY_PATH`.

## Лицензия

MIT
