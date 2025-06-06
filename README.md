# 🎬 Кинотеатр "Сакура" — Автоматизированная информационная система

Это веб-приложение для управления кинотеатром: планирование сеансов, продажа билетов, учет зрителей, аналитика.
Разворачивается в среде Docker (PHP + Symfony, PostgreSQL, pgAdmin, Apache).

---

## 🚀 Быстрый старт

### 📦 Предварительные требования

* Установлен **Docker** и **Docker Compose**
  [Инструкция по установке Docker](https://docs.docker.com/get-docker/)

---

### 🛠️ Как запустить проект

1️⃣ Клонируйте репозиторий:

```bash
git clone https://github.com/Kimitsuro/cinema.git
cd cinema
```

2️⃣ Создайте файл `.env` для настроек Symfony (пример):

```bash
cp .env.example .env
```

В файле `.env` проверьте подключение к базе данных PostgreSQL:

```env
DATABASE_URL=pgsql://admin:admin@db:5432/cinema
```

3️⃣ Запустите контейнеры Docker:

```bash
docker compose up --build
```

⏳ Дождитесь, пока все сервисы поднимутся.

4️⃣ Зайдите в приложение:

* Веб-приложение (Symfony + Apache): [http://localhost:8080](http://localhost:8080)
* pgAdmin для работы с базой данных: [http://localhost:5050](http://localhost:5050)
  Логин: `admin@example.com`
  Пароль: `admin`

5️⃣ Установите зависимости PHP (если требуется):

```bash
docker compose exec web composer install
```

6️⃣ Настройте базу данных:
(только при первом запуске, если нужно создать таблицы)

```bash
docker compose exec web php bin/console doctrine:migrations:migrate
```

7️⃣ Готово! 🎉 Вы можете начать пользоваться приложением.

---

### 🧹 Полезные команды

* Остановить контейнеры:

```bash
docker compose down
```

* Очистить все и начать заново (с удалением томов):

```bash
docker compose down -v
```

---

### 📊 Стек технологий

* **PHP 8.2** (Symfony)
* **Apache 2.4**
* **PostgreSQL 16**
* **pgAdmin 4**
* **Docker**

---

---

### 📌 Дополнительно

* SQL-скрипты для базы данных и хранимых процедур доступны [здесь](https://github.com/Kimitsuro/cinema)

---

🔗 **Проект создан в рамках курсового проекта по дисциплине "Разработка информационных систем"**
Автор: Бобенко Д.С., ЛГТУ, 2025

---
