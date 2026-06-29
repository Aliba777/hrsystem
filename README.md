# HR Connect

<div align="center">

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.1-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-2.4-D22128?style=for-the-badge&logo=apache&logoColor=white)

![Prometheus](https://img.shields.io/badge/Prometheus-monitoring-E6522C?style=flat-square&logo=prometheus&logoColor=white)
![Grafana](https://img.shields.io/badge/Grafana-dashboards-F46800?style=flat-square&logo=grafana&logoColor=white)
![Gemini](https://img.shields.io/badge/Google_Gemini-2.5_Flash-4285F4?style=flat-square&logo=google&logoColor=white)
![Ansible](https://img.shields.io/badge/Ansible-automation-EE0000?style=flat-square&logo=ansible&logoColor=white)
![Telegram](https://img.shields.io/badge/Telegram-alerts-26A5E4?style=flat-square&logo=telegram&logoColor=white)

Платформа для жұмыс іздеушілер мен HR менеджерлерін байланыстыруға арналған / Платформа для соискателей и HR-менеджеров

</div>

---

## О проекте

**HR Connect** — веб-платформа, соединяющая соискателей с HR-менеджерами. Поддерживает двусторонний поиск: соискатели откликаются на вакансии, а HR-менеджеры отправляют офферы прямо на резюме. Интерфейс на казахском языке с поддержкой русского и английского.

### Ключевые возможности

- **Двухролевая система** — отдельный функционал для HR-менеджеров и соискателей
- **Вакансии и резюме** — публикация, поиск, фильтрация
- **Двусторонний найм** — отклик на вакансию (pull) + получение оффера от HR (push)
- **Мессенджер** — real-time чат между HR и соискателем после принятия оффера
- **AI-ассистент** — Google Gemini 2.5 Flash для помощи с резюме, советами к интервью и составлением вакансий
- **Расширенный профиль** — образование, опыт работы, навыки, языки, портфолио
- **Админ-панель** — управление пользователями, вакансиями и откликами
- **Мониторинг** — Prometheus + Grafana + Alertmanager с уведомлениями в Telegram

---

## Стек технологий

| Слой | Технология |
|------|-----------|
| Backend | PHP 8.2 |
| Web-сервер | Apache 2.4 |
| База данных | MySQL 8.0 |
| DB-доступ | PDO (параметрические запросы) |
| Frontend | Bootstrap 5, Font Awesome 6, Vanilla JS |
| AI | Google Gemini 2.5 Flash API |
| Контейнеризация | Docker + Docker Compose |
| DB-менеджер | phpMyAdmin |
| Мониторинг | Prometheus, Grafana, Alertmanager, node-exporter |
| Уведомления | Telegram Bot (webhook) |
| Автоматизация | Ansible, Cron (бэкап БД) |
| IaC | Terraform |

---

## Структура проекта

```
hrsystem/
├── index.php                   # Лендинг (публичная страница)
├── login.php / register.php    # Аутентификация
├── dashboard.php               # Дашборд (HR / соискатель)
├── profile.php / edit_profile.php
├── resumes.php                 # Список резюме (для HR)
├── messages.php                # Чат
│
├── admin/                      # Административная панель
│   ├── dashboard.php
│   ├── users.php
│   ├── vacancies.php
│   └── applications.php
│
├── hr/                         # Функционал HR-менеджера
│   ├── post_vacancy.php        # Публикация вакансии
│   ├── my_vacancies.php
│   ├── applications.php        # Управление откликами
│   ├── my_offers.php           # Отправленные офферы
│   └── ...
│
├── jobseeker/                  # Функционал соискателя
│   ├── browse_vacancies.php    # Поиск вакансий
│   ├── apply.php               # Отклик на вакансию
│   ├── post_resume.php
│   ├── my_resumes.php
│   ├── resume_offers.php       # Полученные офферы
│   └── ...
│
├── ai_assistant/               # AI-ассистент (Gemini)
│   ├── chat.php
│   ├── config.php
│   └── functions/              # interview_tips, resume_helper, vacancy_helper...
│
├── ajax/                       # AJAX-эндпоинты
├── config/                     # БД, SQL-миграции, crontab
├── includes/                   # Общие компоненты (navbar, footer)
├── uploads/                    # Загружаемые файлы пользователей
│
├── prometheus/                 # prometheus.yml, alerts.yml
├── grafana/                    # Provisioning: datasources + dashboards
├── alertmanager/               # alertmanager.yml
├── ansible/                    # playbook.yml + inventory
└── terraform/                  # IaC-конфигурации
```

---

## Быстрый старт

### Требования

- [Docker](https://docs.docker.com/get-docker/) и Docker Compose
- Git

### Установка

1. **Клонировать репозиторий**

```bash
git clone <repository-url>
cd hrsystem
```

2. **Создать файл окружения**

```bash
cp .env.example .env
```

Заполнить `.env`:

```env
DB_HOST=db
DB_NAME=hr_connect
DB_USER=arlan
DB_PASS=your_password

# Telegram-бот для уведомлений мониторинга
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
```

3. **Запустить контейнеры**

```bash
docker compose up -d
```

4. **Импортировать базу данных**

```bash
docker exec -i my_mysql mysql -u arlan -pyour_password hr_connect < config/hr_connect.sql
docker exec -i my_mysql mysql -u arlan -pyour_password hr_connect < config/update_profile_tables.sql
docker exec -i my_mysql mysql -u arlan -pyour_password hr_connect < config/create_resumes_table.sql
docker exec -i my_mysql mysql -u arlan -pyour_password hr_connect < config/create_chat_tables.sql
docker exec -i my_mysql mysql -u arlan -pyour_password hr_connect < config/profile_extended.sql
```

5. **Создать администратора**

```bash
docker exec -it my_php php config/create_admin.php
```

### Сервисы

| Сервис | URL | Описание |
|--------|-----|----------|
| Приложение | http://localhost:8080 | Основное приложение |
| phpMyAdmin | http://localhost:8081 | Управление БД |
| Grafana | http://localhost:3001 | Дашборды мониторинга (admin/admin) |
| Prometheus | http://localhost:9090 | Метрики |
| Alertmanager | http://localhost:9093 | Управление алертами |

---

## База данных

### Основные таблицы

| Таблица | Описание |
|---------|----------|
| `users` | Все пользователи; `user_type`: `hr` \| `job_seeker` |
| `vacancies` | Вакансии, привязанные к HR-пользователю |
| `applications` | Отклики соискателей на вакансии |
| `resumes` | Резюме соискателей |
| `offers` | Офферы от HR на конкретное резюме |
| `conversations` | Чат-беседы (по одной на оффер) |
| `messages` | Сообщения с поддержкой файлов и статусом прочтения |
| `user_education` | Образование |
| `user_experience` | Опыт работы |
| `user_skills` | Навыки с уровнем владения |
| `user_languages` | Языки (A1–C2 / Native) |
| `user_images` | Фото резюме и портфолио |
| `ai_chat_history` | История диалогов с AI-ассистентом |

---

## Мониторинг

Стек мониторинга поднимается автоматически вместе с приложением.

- **Prometheus** собирает метрики каждые 15 секунд, хранит данные 30 дней
- **node-exporter** экспортирует системные метрики хоста (CPU, RAM, диск, сеть)
- **Grafana** предоставляет готовый дашборд `system-metrics`
- **Alertmanager** маршрутизирует алерты в Telegram через `alertmanager_webhook.php`
  - Критические алерты подавляют предупреждения для того же инстанса
  - Повтор уведомлений: каждые 4 часа

### Настройка Telegram-уведомлений

1. Создать бота через [@BotFather](https://t.me/botfather), получить `BOT_TOKEN`
2. Получить `CHAT_ID`:
   ```
   https://api.telegram.org/bot<BOT_TOKEN>/getUpdates
   ```
3. Добавить значения в `.env`

---

## Деплой с Ansible

Для локального деплоя через Docker:

```bash
cd ansible
ansible-playbook -i inventory playbook.yml
```

Плейбук останавливает старый контейнер, пересобирает образ и запускает новый на порту `8080`.

---

## Переменные окружения

| Переменная | Описание | Пример |
|-----------|----------|--------|
| `DB_HOST` | Хост базы данных | `db` |
| `DB_NAME` | Имя базы данных | `hr_connect` |
| `DB_USER` | Пользователь БД | `arlan` |
| `DB_PASS` | Пароль БД | `password` |
| `TELEGRAM_BOT_TOKEN` | Токен Telegram-бота | `123456:ABC-...` |
| `TELEGRAM_CHAT_ID` | ID чата для алертов | `-1001234567890` |

---

## AI-ассистент

Интегрирован **Google Gemini 2.5 Flash**. Поддерживает казахский, русский и английский языки.

Возможности:
- Помощь в написании резюме
- Советы по подготовке к интервью
- Составление описания вакансии
- Общие вопросы о платформе

Для смены модели или промпта — редактировать `ai_assistant/config.php`.

---

## Разработка

### Запуск без Docker

Требования: PHP 8.2+, MySQL 8.0, Apache с `mod_rewrite`.

```bash
# Скопировать файлы в директорию веб-сервера
cp -r . /var/www/html/hrsystem

# Создать БД и импортировать схему
mysql -u root -p -e "CREATE DATABASE hr_connect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p hr_connect < config/hr_connect.sql
```

Обновить `config/database.php` с актуальными параметрами подключения.

### Бэкап базы данных

Cron-задача выполняет автоматический бэкап ежедневно в 02:00:

```bash
# Ручной запуск
docker exec my_php bash scripts/backup_database.sh
```

---

<div align="center">
Made with ❤️ | PHP 8.2 + MySQL + Docker
</div>
