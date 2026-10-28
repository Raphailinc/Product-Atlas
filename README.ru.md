# Product Atlas

Лёгкое API-first приложение для каталога товаров и отзывов. Новый стек: единый фронт на HTML/JS, компактные PHP API, безопасные загрузки, валидация входных данных и авто‑инициализация SQLite. При желании можно переключиться на MySQL через `.env`.

## Что внутри
- `/public/index.php` — современная одностраничная витрина с формами добавления товара и отзывов, поиском и фильтром по категориям.
- `/public/api/*.php` — JSON эндпоинты для товаров, категорий, отзывов и статистики (PDO + строгая валидация).
- `/app/bootstrap.php` — подключение к БД (SQLite по умолчанию, MySQL через переменные окружения), автоприменение схемы для SQLite.
- `/app/schema_sqlite.sql` и `query.sql` — схемы под SQLite и MySQL соответственно.
- `/public/uploads` — хранилище изображений (валидируется тип и размер до 5 МБ).

## Быстрый старт (SQLite, без внешней БД)
```bash
php -S localhost:8000 -t public
# открой http://localhost:8000
```
Приложение само создаст `storage/catalog.db` и необходимые таблицы.

## Подключение MySQL
Создай базу по `query.sql`, затем добавь `.env` в корень:
```env
DB_DSN=mysql:host=localhost;port=3306;dbname=web_catalog;charset=utf8mb4
DB_USER=your_user
DB_PASSWORD=your_password
```
Перезапусти сервер (`php -S localhost:8000 -t public`), API продолжит работать на MySQL.

## Основные эндпоинты
- `GET /api/products.php` — список товаров (`?q=поиск&category_id=ID&limit=30`).
- `POST /api/products.php` — создать товар (`name`, `description`, `price`, `category_id` или `category_name`, `image`).
- `GET /api/categories.php` / `POST /api/categories.php` — получить/создать категорию.
- `GET /api/reviews.php?product_id=ID` — отзывы товара, `POST /api/reviews.php` — добавить отзыв (`product_id`, `rating 1..5`, `comment`).
- `GET /api/stats.php` — счётчики и последние товары.

Все ответы — JSON, ошибки возвращаются с понятным сообщением и статусом.

## UI‑фишки
- Поиск и фильтрация без перезагрузки, карточки товаров с рейтингом и категорией.
- Формы валидации на клиенте и сервере, понятные тосты с результатом.
- Стиль: тёмный градиент, акцентные зелёно‑оранжевые штрихи, нестандартный шрифт Space Grotesk.

## Проверка кода
- PHP синтаксис: `find public app -name '*.php' -print0 | xargs -0 -n1 php -l`
- Локальный запуск: `php -S localhost:8000 -t public`

MIT © Raphailinc
