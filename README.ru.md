# Product Atlas

[English](README.md) · [Русский](README.ru.md)

**Архивный учебный проект.** Сохранён как часть истории обучения. Актуальные backend-проекты — в [профиле](https://github.com/Raphailinc).

Учебное PHP-приложение: JSON API товаров, категорий и отзывов с небольшим HTML/JavaScript-интерфейсом.

## Возможности

- Поиск товаров и фильтрация по категории.
- Создание товаров и отзывов через JSON API.
- Проверка типа и размера загружаемых изображений.
- Автоматическая инициализация SQLite либо MySQL через .env.

## Локальный запуск

```bash
# Requires PHP 8+ with PDO SQLite enabled.
php -S localhost:8000 -t public
```

## Проверки

```bash
find public app -name '*.php' -print0 | xargs -0 -n1 php -l
```

## Условия запуска и границы проекта

Интерфейс: http://localhost:8000. Нужен PHP 8+ с PDO SQLite. Для MySQL примените query.sql и задайте DB_DSN, DB_USER, DB_PASSWORD. Команда проверки выполняет только синтаксический разбор PHP; поведенческих автотестов нет.
