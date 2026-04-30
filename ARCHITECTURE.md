# 🏗️ Архитектура HR Connect Infrastructure

## Общая схема

```
┌─────────────────────────────────────────────────────────────────┐
│                         HR Connect Platform                      │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                      Docker Environment                          │
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   Web (PHP)  │  │  MySQL 8.0   │  │ phpMyAdmin   │         │
│  │   Apache     │  │   Database   │  │              │         │
│  │   Port 8080  │  │   Port 3306  │  │  Port 8081   │         │
│  └──────┬───────┘  └──────┬───────┘  └──────────────┘         │
│         │                  │                                     │
│         │                  │                                     │
│  ┌──────▼──────────────────▼─────────────────────────┐         │
│  │           Docker Network (bridge)                  │         │
│  └────────────────────────────────────────────────────┘         │
│                                                                  │
│  ┌────────────────────────────────────────────────────┐         │
│  │              Persistent Volumes                     │         │
│  │  • db_data (MySQL data)                            │         │
│  │  • backup_data (Database backups)                  │         │
│  └────────────────────────────────────────────────────┘         │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    Infrastructure Components                     │
│                                                                  │
│  ┌──────────────────┐  ┌──────────────────┐                    │
│  │  Backup Script   │  │  Telegram Bot    │                    │
│  │  (Cron Daily)    │──▶│  Notifications   │                    │
│  │  02:00 AM        │  │                  │                    │
│  └──────────────────┘  └────────┬─────────┘                    │
│                                  │                               │
│                                  ▼                               │
│                         ┌────────────────┐                      │
│                         │  Telegram API  │                      │
│                         │  (External)    │                      │
│                         └────────────────┘                      │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    Infrastructure as Code                        │
│                                                                  │
│  ┌──────────────────────────────────────────────────┐           │
│  │              Terraform Configuration              │           │
│  │  • VPS Provisioning (DigitalOcean)               │           │
│  │  • Firewall Rules                                │           │
│  │  • Docker Installation                           │           │
│  │  • SSH Key Management                            │           │
│  └──────────────────────────────────────────────────┘           │
└─────────────────────────────────────────────────────────────────┘
```

---

## Компоненты системы

### 1. Web Container (my_php)

**Образ:** PHP 8.2 + Apache  
**Порт:** 8080  
**Функции:**
- Веб-приложение HR Connect
- Backup скрипт (cron)
- Telegram notifier
- Логирование

**Установленные пакеты:**
- PHP 8.2 с расширениями (pdo, pdo_mysql)
- Apache 2.4
- Cron
- MySQL Client
- Curl

**Volumes:**
- `./:/var/www/html/` — код приложения
- `backup_data:/var/backups/hr_connect` — бэкапы БД

**Environment Variables:**
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`

---

### 2. Database Container (my_mysql)

**Образ:** MySQL 8.0  
**Порт:** 3306  
**Функции:**
- Хранение данных приложения
- Таблицы: users, vacancies, applications, ai_chat_history

**Volume:**
- `db_data:/var/lib/mysql` — persistent storage

**Credentials:**
- Root: `root` / `root`
- User: `arlan` / `password`
- Database: `hr_connect`

---

### 3. phpMyAdmin Container (my_pma)

**Образ:** phpMyAdmin  
**Порт:** 8081  
**Функции:**
- Веб-интерфейс для управления БД
- Просмотр таблиц и данных
- Выполнение SQL запросов

---

### 4. Backup System

**Компоненты:**
- `scripts/backup_database.sh` — bash скрипт
- `config/crontab` — расписание
- `/var/backups/hr_connect/` — хранилище

**Процесс:**
```
┌─────────────┐
│ Cron (02:00)│
└──────┬──────┘
       │
       ▼
┌─────────────────────────┐
│ backup_database.sh      │
│ 1. Check DB connection  │
│ 2. mysqldump            │
│ 3. gzip compression     │
│ 4. Save to volume       │
│ 5. Delete old (>30d)    │
│ 6. Send Telegram alert  │
│ 7. Log result           │
└─────────────────────────┘
       │
       ▼
┌─────────────────────────┐
│ Backup File             │
│ hr_connect_YYYYMMDD_    │
│ HHMMSS.sql.gz           │
└─────────────────────────┘
```

**Retention Policy:**
- Хранение: 30 дней
- Формат: `hr_connect_YYYYMMDD_HHMMSS.sql.gz`
- Сжатие: gzip level 6
- Размер: ~1-5 MB (зависит от данных)

---

### 5. Telegram Notification System

**Компоненты:**
- `scripts/TelegramNotifier.php` — PHP класс
- `scripts/telegram_notify.php` — CLI wrapper

**Процесс отправки:**
```
┌─────────────────┐
│ Application     │
│ (Backup, etc.)  │
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│ TelegramNotifier.php    │
│ 1. Format message       │
│ 2. Check deduplication  │
│ 3. Send to API          │
│ 4. Retry if failed      │
│ 5. Log result           │
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────┐
│ Telegram Bot API        │
│ api.telegram.org        │
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────┐
│ User's Telegram         │
│ (Notification received) │
└─────────────────────────┘
```

**Severity Levels:**
- 🔵 **INFO** — Информационные сообщения
- 🟡 **WARNING** — Предупреждения
- 🔴 **ERROR** — Ошибки
- 🚨 **CRITICAL** — Критические алерты

**Retry Logic:**
- Попытка 1: немедленно
- Попытка 2: через 1 секунду
- Попытка 3: через 2 секунды
- Попытка 4: через 4 секунды
- Итого: 3 retry с exponential backoff

**Deduplication:**
- Окно: 60 секунд
- Механизм: MD5 hash сообщения
- Хранение: в памяти + файл кэша

---

### 6. Terraform Infrastructure

**Провайдер:** DigitalOcean (можно адаптировать для AWS/Hetzner)

**Создаваемые ресурсы:**
```
┌─────────────────────────────────────┐
│ DigitalOcean Droplet                │
│ • Ubuntu 22.04 LTS                  │
│ • 2 vCPU, 4GB RAM, 80GB SSD         │
│ • Region: Frankfurt (fra1)          │
│ • Docker + Docker Compose           │
│ • Git                               │
└─────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ Firewall Rules                      │
│ • Port 22 (SSH)                     │
│ • Port 80 (HTTP)                    │
│ • Port 443 (HTTPS)                  │
│ • Port 8080 (Web App)               │
│ • Port 8081 (phpMyAdmin)            │
└─────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ SSH Key Access                      │
│ • Public key authentication         │
│ • No password login                 │
└─────────────────────────────────────┘
```

**Outputs:**
- Public IP address
- SSH connection string
- Web URL
- phpMyAdmin URL

---

## Потоки данных

### Backup Flow

```
┌──────┐    mysqldump    ┌──────────┐    gzip    ┌──────────┐
│ MySQL│ ──────────────▶ │ SQL Dump │ ─────────▶ │ .sql.gz  │
└──────┘                 └──────────┘            └──────────┘
                                                       │
                                                       ▼
                                              ┌─────────────────┐
                                              │ /var/backups/   │
                                              │ hr_connect/     │
                                              └─────────────────┘
                                                       │
                                                       ▼
                                              ┌─────────────────┐
                                              │ Telegram Alert  │
                                              │ (Success/Fail)  │
                                              └─────────────────┘
```

### Notification Flow

```
┌──────────┐    format    ┌──────────┐    HTTP    ┌──────────┐
│ Event    │ ───────────▶ │ Message  │ ─────────▶ │ Telegram │
│ (Backup) │              │ (JSON)   │            │ API      │
└──────────┘              └──────────┘            └──────────┘
                                                       │
                                                       ▼
                                              ┌─────────────────┐
                                              │ User Device     │
                                              │ (Push Notif.)   │
                                              └─────────────────┘
```

---

## Безопасность

### Уровни защиты

1. **Docker Network Isolation**
   - Контейнеры изолированы в bridge сети
   - Внешний доступ только через exposed порты

2. **Environment Variables**
   - Sensitive данные в `.env` (не в git)
   - Docker secrets для production

3. **Backup Security**
   - Директория с правами 700 (только owner)
   - Опционально: GPG шифрование

4. **Telegram Bot**
   - Token никогда не логируется
   - Retry без exposure токена в логах

5. **Firewall (Terraform)**
   - Только необходимые порты открыты
   - SSH, HTTP, HTTPS, App ports

---

## Мониторинг и логирование

### Log Files

| Файл | Назначение | Ротация |
|------|-----------|---------|
| `/var/log/hr_connect_backup.log` | Backup операции | Daily |
| `/var/log/hr_connect_telegram.log` | Telegram уведомления | Daily |
| `/var/log/apache2/access.log` | HTTP запросы | Weekly |
| `/var/log/apache2/error.log` | HTTP ошибки | Weekly |

### Metrics (будущее)

- CPU usage (Prometheus + Node Exporter)
- Memory usage
- Disk usage
- Network traffic
- Container health
- Backup success rate

---

## Масштабирование

### Горизонтальное

- Load balancer перед web контейнерами
- Несколько web instances
- Shared storage для uploads

### Вертикальное

- Увеличение ресурсов контейнеров
- Больше CPU/RAM для MySQL
- SSD для faster I/O

### Географическое

- Multi-region deployment (Terraform)
- CDN для статики
- Database replication

---

## Следующие шаги

### Priority 2 (Medium)

1. **Nginx Reverse Proxy**
   - SSL/HTTPS termination
   - Load balancing
   - Caching

2. **Fail2Ban**
   - SSH brute-force protection
   - Auto-ban malicious IPs

3. **n8n Workflow**
   - Jenkins integration
   - Telegram automation
   - Scheduled tasks

### Priority 3 (Complex)

4. **Prometheus + Node Exporter**
   - Metrics collection
   - Time-series database

5. **Grafana**
   - Visualization dashboards
   - Real-time monitoring

6. **Jenkins**
   - CI/CD pipeline
   - Automated testing
   - Deployment automation

7. **Alertmanager**
   - Alert routing
   - Notification rules
   - Silence management
