<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Atlas</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <div class="background-gradient"></div>
    <header class="hero">
        <div>
            <p class="eyebrow">Product Atlas · лёгкая витрина каталога</p>
            <h1>Быстро обновляй товары, отзывы и категории</h1>
            <p class="lede">Мини-приложение на PHP + SQLite/MySQL с API-first подходом, удобными формами и чистыми JSON эндпоинтами для автоматизации.</p>
            <div class="hero-actions">
                <a class="button primary" href="#products">Смотреть каталог</a>
                <a class="button ghost" href="#create">Добавить товар</a>
            </div>
        </div>
        <div class="hero-panel">
            <div class="badge">Realtime API</div>
            <p class="hero-subtitle">/api/products.php</p>
            <pre><code id="api-preview">curl http://localhost:8000/api/products.php</code></pre>
            <p class="muted">Все данные доступны через JSON. Запросы безопасно валидируются и сохраняются через PDO.</p>
        </div>
    </header>

    <main class="layout">
        <section class="stats" id="stats">
            <div class="stat-card">
                <p class="muted">Товаров</p>
                <p class="number" data-stat="products">—</p>
            </div>
            <div class="stat-card">
                <p class="muted">Категорий</p>
                <p class="number" data-stat="categories">—</p>
            </div>
            <div class="stat-card">
                <p class="muted">Отзывов</p>
                <p class="number" data-stat="reviews">—</p>
            </div>
            <div class="stat-card recent" id="recent">
                <p class="muted">Последние добавления</p>
                <ul></ul>
            </div>
        </section>

        <section class="grid" id="create">
            <div class="card form-card">
                <div class="card-header">
                    <div>
                        <p class="eyebrow">Новый товар</p>
                        <h3>Добавить позицию каталога</h3>
                    </div>
                    <span class="badge">POST /api/products.php</span>
                </div>
                <form id="product-form">
                    <div class="field">
                        <label for="name">Название</label>
                        <input required id="name" name="name" placeholder="Например, Raspberry Pi 5">
                    </div>
                    <div class="field">
                        <label for="description">Описание</label>
                        <textarea id="description" name="description" rows="3" placeholder="Кратко чем полезен товар"></textarea>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label for="price">Цена, ₽</label>
                            <input required type="number" step="0.01" min="0" id="price" name="price" placeholder="1999.00">
                        </div>
                        <div class="field">
                            <label for="category">Категория</label>
                            <select id="category" name="category_id"></select>
                        </div>
                    </div>
                    <div class="field">
                        <label for="category_name">Новая категория (по желанию)</label>
                        <input id="category_name" name="category_name" placeholder="Если категории нет в списке">
                    </div>
                    <div class="field file-field">
                        <label for="image">Изображение (до 5 МБ)</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>
                    <button class="button primary" type="submit">Сохранить товар</button>
                </form>
            </div>

            <div class="card form-card">
                <div class="card-header">
                    <div>
                        <p class="eyebrow">Оценки</p>
                        <h3>Добавить отзыв</h3>
                    </div>
                    <span class="badge">POST /api/reviews.php</span>
                </div>
                <form id="review-form">
                    <div class="field">
                        <label for="review-product">Товар</label>
                        <select id="review-product" name="product_id"></select>
                    </div>
                    <div class="field-grid">
                        <div class="field">
                            <label for="rating">Рейтинг</label>
                            <input required type="number" min="1" max="5" id="rating" name="rating" value="5">
                        </div>
                        <div class="field">
                            <label for="comment">Комментарий</label>
                            <input id="comment" name="comment" placeholder="Чем хорош товар?">
                        </div>
                    </div>
                    <button class="button ghost" type="submit">Сохранить отзыв</button>
                </form>
                <p class="muted small">Рейтинг валидируется на стороне сервера, а отзывы связываются с существующими товарами.</p>
            </div>
        </section>

        <section class="card" id="filters">
            <div class="card-header">
                <h3>Каталог</h3>
                <div class="filters">
                    <input id="search" type="search" placeholder="Поиск по названию или описанию">
                    <select id="filter-category">
                        <option value="">Все категории</option>
                    </select>
                    <button class="button ghost" id="refresh">Обновить</button>
                </div>
            </div>
            <div id="products" class="product-grid"></div>
        </section>
    </main>

    <div id="toast" class="toast" hidden></div>

    <script src="assets/app.js" defer></script>
</body>
</html>
