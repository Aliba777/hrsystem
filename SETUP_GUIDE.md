# 📖 Детальное руководство по настройке HR Connect Infrastructure

## Содержание

1. [Предварительные требования](#предварительные-требования)
2. [Установка Docker](#установка-docker)
3. [Настройка Telegram бота](#настройка-telegram-бота)
4. [Конфигурация проекта](#конфигурация-проекта)
5. [Запуск и тестирование](#запуск-и-тестирование)
6. [Настройка Terraform](#настройка-terraform)
7. [Troubleshooting](#troubleshooting)

---

## Предварительные требования

### Системные требования

- **OS:** Linux (Ubuntu 20.04+), macOS, Windows 10+ с WSL2
- **RAM:** Минимум 4GB (рекомендуется 8GB)
- **Disk:** Минимум 10GB свободного места
- **CPU:** 2+ cores

### Необходимое ПО

- **Docker:** 20.10+
- **Docker Compose:** 2.0+
- **Git:** 2.0+
- **Terraform:** 1.0+ (опционально, для cloud deployment)
- **Текстовый редактор:** VS Code, Sublime, nano, vim

---

## Установка Docker

### Ubuntu/Debian

```bash
# Обновите систему
sudo apt update
sudo apt upgrade -y

# Установите зависимости
sudo apt install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

# Добавьте Docker GPG ключ
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Добавьте Docker репозиторий
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Установите Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Добавьте пользователя в группу docker
sudo usermod -aG docker $USER

# Перелогиньтесь или выполните
newgrp docker

# Проверьте установку
docker --version
docker compose version
```

### macOS

```bash
# Установите Docker Desktop
# Скачайте с: https://www.docker.com/products/docker-desktop

# Или через Homebrew
brew install --cask docker

# Запустите Docker Desktop
open -a Docker

# Проверьте установку
docker --version
docker compose version
```

### Windows (WSL2)

1. Установите WSL2: https://docs.microsoft.com/en-us/windows/wsl/install
2. Установите Docker Desktop: https://www.docker.com/products/docker-desktop
3. Включите WSL2 integration в Docker Desktop settings
4. Откройте WSL2 terminal и проверьте:
   ```bash
   docker --version
   docker compose version
   ```

---

## Настройка Telegram бота

### Шаг 1: Создание бота

1. Откройте Telegram на телефоне или в веб-версии
2. Найдите `@BotFather` (официальный бот для создания ботов)
3. Отправьте команду: `/start`
4. Отправьте команду: `/newbot`
5. Введите **имя бота** (например: `HR Connect Notifications`)
6. Введите **username бота** (должен заканчиваться на `bot`, например: `hr_connect_notif_bot`)
7. **Сохраните токен** — он выглядит так:
   ```
   1234567890:ABCdefGHIjklMNOpqrsTUVwxyz-1234567
   ```

### Шаг 2: Получение Chat ID

#### Метод 1: Через API

1. Отправьте любое сообщение вашему боту (например: "Hello")
2. Откройте в браузере (замените `YOUR_BOT_TOKEN`):
   ```
   https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates
   ```
3. Найдите в JSON ответе:
   ```json
   "chat": {
     "id": 123456789,
     "first_name": "Your Name",
     "type": "private"
   }
   ```
4. **Сохраните Chat ID** (в примере: `123456789`)

#### Метод 2: Через бота @userinfobot

1. Найдите в Telegram: `@userinfobot`
2. Отправьте команду: `/start`
3. Бот пришлёт ваш Chat ID

### Шаг 3: Тестирование бота

Проверьте, что бот работает:

```bash
# Замените YOUR_BOT_TOKEN и YOUR_CHAT_ID
curl -X POST "https://api.telegram.org/botYOUR_BOT_TOKEN/sendMessage" \
  -d "chat_id=YOUR_CHAT_ID" \
  -d "text=Test message from HR Connect"
```

Вы должны получить сообщение в Telegram!

---

## Конфигурация проекта

### Шаг 1: Клонирование репозитория

```bash
# Если ещё не клонировали
git clone <your-repo-url>
cd hr-connect
```

### Шаг 2: Создание .env файла

```bash
# Скопируйте пример
cp .env.example .env

# Откройте в редакторе
nano .env
# или
code .env
```

### Шаг 3: Заполнение .env

Вставьте ваши данные:

```env
# Database Configuration
DB_HOST=db
DB_NAME=hr_connect
DB_USER=arlan
DB_PASS=password

# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz-1234567
TELEGRAM_CHAT_ID=123456789
```

**⚠️ Важно:**
- Замените `TELEGRAM_BOT_TOKEN` на ваш токен из BotFather
- Замените `TELEGRAM_CHAT_ID` на ваш Chat ID
- Не коммитьте `.env` в git!

### Шаг 4: Проверка конфигурации

```bash
# Проверьте, что .env создан
ls -la .env

# Проверьте содержимое (без вывода в терминал)
cat .env | grep TELEGRAM
```

---

## Запуск и тестирование

### Шаг 1: Сборка контейнеров

```bash
# Соберите образы
docker-compose build

# Это займёт 2-5 минут при первом запуске
```

**Что происходит:**
- Скачивается базовый образ PHP 8.2 + Apache
- Устанавливаются пакеты: cron, mysql-client
- Устанавливаются PHP расширения: pdo, pdo_mysql
- Копируется crontab конфигурация

### Шаг 2: Запуск контейнеров

```bash
# Запустите в фоновом режиме
docker-compose up -d

# Проверьте статус
docker-compose ps
```

**Ожидаемый вывод:**
```
NAME       IMAGE              STATUS         PORTS
my_php     hr-connect-web     Up 10 seconds  0.0.0.0:8080->80/tcp
my_mysql   mysql:8.0          Up 10 seconds  0.0.0.0:3306->3306/tcp
my_pma     phpmyadmin         Up 10 seconds  0.0.0.0:8081->80/tcp
```

### Шаг 3: Проверка веб-приложения

Откройте в браузере:

- **Приложение:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081
  - Server: `db`
  - Username: `arlan`
  - Password: `password`

### Шаг 4: Тест Telegram уведомлений

```bash
# Отправьте тестовое уведомление
docker exec -it my_php php /var/www/html/scripts/telegram_notify.php INFO "🚀 HR Connect запущен успешно!"
```

**✅ Проверьте Telegram — должно прийти сообщение!**

### Шаг 5: Тест backup скрипта

```bash
# Запустите backup вручную
docker exec -it my_php /var/www/html/scripts/backup_database.sh
```

**✅ Проверьте:**
1. Telegram — должно прийти уведомление о успешном бэкапе
2. Файл бэкапа создан:
   ```bash
   docker exec -it my_php ls -lh /var/backups/hr_connect/
   ```

### Шаг 6: Проверка логов

```bash
# Логи бэкапа
docker exec -it my_php tail -20 /var/log/hr_connect_backup.log

# Логи Telegram
docker exec -it my_php tail -20 /var/log/hr_connect_telegram.log

# Логи Docker
docker-compose logs --tail=50 web
```

### Шаг 7: Проверка cron

```bash
# Проверьте, что cron запущен
docker exec -it my_php ps aux | grep cron

# Проверьте crontab
docker exec -it my_php crontab -l
```

**Ожидаемый вывод:**
```
0 2 * * * root /var/www/html/scripts/backup_database.sh >> /var/log/hr_connect_backup.log 2>&1
```

---

## Настройка Terraform

### Шаг 1: Установка Terraform

#### Ubuntu/Debian

```bash
wget -O- https://apt.releases.hashicorp.com/gpg | sudo gpg --dearmor -o /usr/share/keyrings/hashicorp-archive-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/hashicorp-archive-keyring.gpg] https://apt.releases.hashicorp.com $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/hashicorp.list
sudo apt update && sudo apt install terraform
```

#### macOS

```bash
brew tap hashicorp/tap
brew install hashicorp/tap/terraform
```

#### Проверка

```bash
terraform --version
```

### Шаг 2: Создание DigitalOcean аккаунта

1. Зарегистрируйтесь: https://www.digitalocean.com/
2. Подтвердите email
3. Добавьте платёжный метод

### Шаг 3: Создание API токена

1. Перейдите: https://cloud.digitalocean.com/account/api/tokens
2. Нажмите "Generate New Token"
3. Имя: `HR Connect Terraform`
4. Права: **Read & Write**
5. **Сохраните токен** (показывается только один раз!)

### Шаг 4: Загрузка SSH ключа

```bash
# Создайте SSH ключ (если нет)
ssh-keygen -t ed25519 -C "your_email@example.com"

# Скопируйте публичный ключ
cat ~/.ssh/id_ed25519.pub
```

1. Перейдите: https://cloud.digitalocean.com/account/security
2. Нажмите "Add SSH Key"
3. Вставьте содержимое `id_ed25519.pub`
4. Имя: `My Laptop`
5. **Запомните имя ключа!**

### Шаг 5: Конфигурация Terraform

```bash
cd terraform

# Скопируйте пример
cp terraform.tfvars.example terraform.tfvars

# Отредактируйте
nano terraform.tfvars
```

Заполните:

```hcl
do_token         = "dop_v1_abc123..."  # Ваш API токен
environment_name = "production"
region           = "fra1"              # Frankfurt
instance_type    = "s-2vcpu-4gb"       # 2 CPU, 4GB RAM
ssh_key_name     = "My Laptop"         # Имя вашего SSH ключа
```

### Шаг 6: Инициализация и применение

```bash
# Инициализация
terraform init

# Проверка конфигурации
terraform validate

# Предпросмотр изменений
terraform plan

# Применение (создание сервера)
terraform apply
```

Введите `yes` для подтверждения.

**⏱️ Создание займёт 2-3 минуты**

### Шаг 7: Получение outputs

```bash
terraform output
```

**Пример вывода:**
```
instance_public_ip = "123.456.789.012"
ssh_connection_string = "ssh root@123.456.789.012"
web_url = "http://123.456.789.012:8080"
```

### Шаг 8: Подключение к серверу

```bash
# Используйте команду из output
ssh root@123.456.789.012

# Проверьте Docker
docker --version
docker-compose --version
```

---

## Troubleshooting

### Проблема: Контейнеры не запускаются

**Симптомы:**
```bash
docker-compose ps
# Показывает Exit 1 или Restarting
```

**Решение:**

```bash
# Посмотрите логи
docker-compose logs web

# Пересоберите с нуля
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Проблема: Telegram уведомления не приходят

**Симптомы:**
- Скрипт выполняется, но сообщений нет

**Решение:**

```bash
# 1. Проверьте переменные окружения
docker exec -it my_php env | grep TELEGRAM

# Если пусто:
docker-compose down
docker-compose up -d

# 2. Проверьте токен вручную
curl "https://api.telegram.org/bot<YOUR_TOKEN>/getMe"

# 3. Проверьте логи
docker exec -it my_php tail -f /var/log/hr_connect_telegram.log

# 4. Тест отправки
docker exec -it my_php php /var/www/html/scripts/telegram_notify.php INFO "Test"
```

### Проблема: Backup не создаётся

**Симптомы:**
- Скрипт выполняется, но файлов нет

**Решение:**

```bash
# 1. Проверьте подключение к БД
docker exec -it my_php mysql -h db -u arlan -ppassword hr_connect -e "SELECT 1"

# 2. Проверьте права на папку
docker exec -it my_php ls -ld /var/backups/hr_connect/

# 3. Создайте папку вручную
docker exec -it my_php mkdir -p /var/backups/hr_connect
docker exec -it my_php chmod 700 /var/backups/hr_connect

# 4. Запустите backup
docker exec -it my_php /var/www/html/scripts/backup_database.sh
```

### Проблема: Cron не запускается

**Симптомы:**
- Backup не выполняется автоматически в 02:00

**Решение:**

```bash
# 1. Проверьте процесс cron
docker exec -it my_php ps aux | grep cron

# Если нет процесса:
docker exec -it my_php cron

# 2. Проверьте crontab
docker exec -it my_php crontab -l

# 3. Проверьте логи cron
docker exec -it my_php tail -f /var/log/cron.log

# 4. Пересоберите контейнер
docker-compose build web
docker-compose up -d web
```

### Проблема: Terraform authentication failed

**Симптомы:**
```
Error: Error creating droplet: POST https://api.digitalocean.com/v2/droplets: 401 Unable to authenticate you
```

**Решение:**

```bash
# 1. Проверьте токен
cat terraform.tfvars | grep do_token

# 2. Создайте новый токен
# https://cloud.digitalocean.com/account/api/tokens

# 3. Обновите terraform.tfvars
nano terraform.tfvars

# 4. Попробуйте снова
terraform plan
```

### Проблема: SSH key not found

**Симптомы:**
```
Error: Error creating droplet: SSH Key is invalid
```

**Решение:**

```bash
# 1. Проверьте имя ключа
cat terraform.tfvars | grep ssh_key_name

# 2. Список ключей в DigitalOcean
# https://cloud.digitalocean.com/account/security

# 3. Загрузите ключ
cat ~/.ssh/id_ed25519.pub
# Скопируйте и загрузите на DigitalOcean

# 4. Обновите имя в terraform.tfvars
nano terraform.tfvars
```

---

## Дополнительные команды

### Docker

```bash
# Остановить все контейнеры
docker-compose down

# Перезапустить контейнер
docker-compose restart web

# Посмотреть логи в реальном времени
docker-compose logs -f

# Войти в контейнер
docker exec -it my_php bash

# Очистить всё (ОСТОРОЖНО!)
docker-compose down -v
docker system prune -a
```

### Backup

```bash
# Список всех бэкапов
docker exec -it my_php ls -lh /var/backups/hr_connect/

# Размер папки с бэкапами
docker exec -it my_php du -sh /var/backups/hr_connect/

# Восстановление из бэкапа
docker exec -it my_php bash -c "gunzip -c /var/backups/hr_connect/hr_connect_20260430_020000.sql.gz | mysql -h db -u arlan -ppassword hr_connect"

# Скачать бэкап на хост
docker cp my_php:/var/backups/hr_connect/hr_connect_20260430_020000.sql.gz ./
```

### Terraform

```bash
# Форматирование кода
terraform fmt

# Валидация
terraform validate

# План без применения
terraform plan

# Применить изменения
terraform apply

# Удалить инфраструктуру
terraform destroy

# Показать outputs
terraform output

# Показать state
terraform show
```

---

## Следующие шаги

После успешной настройки базовых компонентов, можно добавить:

1. **Nginx Reverse Proxy** — SSL/HTTPS, load balancing
2. **Fail2Ban** — защита от брутфорса
3. **n8n** — автоматизация воркфлоу
4. **Prometheus + Grafana** — мониторинг
5. **Jenkins** — CI/CD pipeline

Инструкции по установке этих компонентов будут добавлены позже.

---

## Полезные ссылки

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Terraform Documentation](https://www.terraform.io/docs)
- [DigitalOcean API](https://docs.digitalocean.com/reference/api/)
- [Telegram Bot API](https://core.telegram.org/bots/api)
- [MySQL Documentation](https://dev.mysql.com/doc/)

---

## Поддержка

Если возникли проблемы:
1. Проверьте раздел [Troubleshooting](#troubleshooting)
2. Посмотрите логи: `docker-compose logs`
3. Проверьте статус: `docker-compose ps`
4. Проверьте `.env` файл
5. Пересоберите контейнеры: `docker-compose build --no-cache`
