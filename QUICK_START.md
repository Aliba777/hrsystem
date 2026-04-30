# 🚀 Быстрый старт - HR Connect Infrastructure

## Шаг 1: Создание Telegram бота (5 минут)

### 1.1 Создайте бота через BotFather

1. Откройте Telegram
2. Найдите `@BotFather`
3. Отправьте команду: `/newbot`
4. Введите имя бота: `HR Connect Bot`
5. Введите username бота: `hr_connect_bot` (или любой доступный)
6. **Скопируйте токен** (выглядит так: `1234567890:ABCdefGHIjklMNOpqrsTUVwxyz`)

### 1.2 Получите Chat ID

1. Отправьте любое сообщение вашему боту в Telegram
2. Откройте в браузере (замените `YOUR_TOKEN`):
   ```
   https://api.telegram.org/botYOUR_TOKEN/getUpdates
   ```
3. Найдите `"chat":{"id":123456789` — это ваш Chat ID
4. **Скопируйте Chat ID**

---

## Шаг 2: Настройка проекта (2 минуты)

### 2.1 Создайте файл .env

```bash
cp .env.example .env
```

### 2.2 Отредактируйте .env

Откройте файл `.env` и вставьте ваши данные:

```env
# Database Configuration
DB_HOST=db
DB_NAME=hr_connect
DB_USER=arlan
DB_PASS=password

# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_CHAT_ID=123456789
```

**⚠️ Важно:** Замените `TELEGRAM_BOT_TOKEN` и `TELEGRAM_CHAT_ID` на ваши реальные значения!

---

## Шаг 3: Запуск Docker контейнеров (3 минуты)

### 3.1 Пересоберите контейнеры

```bash
docker-compose build
```

Это займёт 2-3 минуты при первом запуске.

### 3.2 Запустите контейнеры

```bash
docker-compose up -d
```

### 3.3 Проверьте статус

```bash
docker-compose ps
```

Вы должны увидеть 3 контейнера в статусе `Up`:
- `my_php` (web)
- `my_mysql` (db)
- `my_pma` (phpmyadmin)

---

## Шаг 4: Тестирование (5 минут)

### 4.1 Проверьте веб-приложение

Откройте в браузере:
- **Приложение:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081

### 4.2 Протестируйте Telegram уведомления

```bash
docker exec -it my_php php /var/www/html/scripts/telegram_notify.php INFO "Тест: HR Connect запущен!"
```

**✅ Вы должны получить сообщение в Telegram!**

### 4.3 Протестируйте backup скрипт

```bash
docker exec -it my_php /var/www/html/scripts/backup_database.sh
```

**✅ Вы должны получить уведомление о успешном бэкапе в Telegram!**

### 4.4 Проверьте созданный бэкап

```bash
docker exec -it my_php ls -lh /var/backups/hr_connect/
```

Вы должны увидеть файл вида: `hr_connect_20260430_123456.sql.gz`

---

## Шаг 5: Проверка логов

### Логи бэкапа

```bash
docker exec -it my_php tail -f /var/log/hr_connect_backup.log
```

### Логи Telegram

```bash
docker exec -it my_php tail -f /var/log/hr_connect_telegram.log
```

### Логи Docker

```bash
docker-compose logs -f web
```

---

## 🎉 Готово!

Теперь у вас работает:
- ✅ Автоматический бэкап БД (каждый день в 02:00)
- ✅ Telegram уведомления
- ✅ Логирование всех операций

---

## 🔧 Что дальше?

### Настройка Terraform (опционально)

Если хотите развернуть на реальном сервере:

1. Перейдите в папку `terraform/`
2. Следуйте инструкциям в `terraform/README.md`
3. Создайте VPS на DigitalOcean

### Добавление новых компонентов

Следующие компоненты для установки:
- **Nginx Reverse Proxy** (SSL/HTTPS)
- **Fail2Ban** (защита SSH)
- **n8n** (автоматизация)
- **Prometheus + Grafana** (мониторинг)
- **Jenkins** (CI/CD)

---

## ❓ Проблемы?

### Telegram уведомления не приходят

```bash
# Проверьте переменные окружения
docker exec -it my_php env | grep TELEGRAM

# Если пусто - перезапустите контейнеры
docker-compose restart web
```

### Бэкап не создаётся

```bash
# Проверьте подключение к БД
docker exec -it my_php mysql -h db -u arlan -ppassword hr_connect -e "SELECT 1"

# Проверьте права на папку
docker exec -it my_php ls -ld /var/backups/hr_connect/
```

### Контейнеры не запускаются

```bash
# Посмотрите логи
docker-compose logs web

# Пересоберите с нуля
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

---

## 📚 Дополнительная документация

- **Полная документация:** `INFRASTRUCTURE_README.md`
- **Terraform:** `terraform/README.md`
- **Архитектура:** `ARCHITECTURE.md` (будет создан)
- **Детальная настройка:** `SETUP_GUIDE.md` (будет создан)
