# Product Atlas

[English](README.md) · [Русский](README.ru.md)

**Archived learning project.** Preserved as part of my learning history. For current backend work, visit my [profile](https://github.com/Raphailinc).

An API-first portfolio application for product records, categories and reviews, with a small HTML/JavaScript interface.

## What it does

- Search products and filter them by category.
- Create products and reviews through PHP JSON endpoints.
- Validate uploaded image type and size.
- Initialize SQLite automatically or configure MySQL through `.env`.

## Run locally

```bash
# Requires PHP 8+ with PDO SQLite enabled.
php -S localhost:8000 -t public
```

## Checks

```bash
find public app -name '*.php' -print0 | xargs -0 -n1 php -l
```

## Scope and implementation notes

Local UI: `http://localhost:8000`. For MySQL, initialize the schema in `query.sql` and configure `DB_DSN`, `DB_USER` and `DB_PASSWORD`. Syntax checks are available; an automated behavioral test suite is not included.
