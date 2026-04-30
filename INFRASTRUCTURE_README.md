# HR Connect - Infrastructure Components

This document describes the infrastructure components added to the HR Connect project.

## 📦 Components Overview

### Priority 1 (Easy) - ✅ Implemented

1. **Database Backup Script** - Automated MySQL backups with cron scheduling
2. **Telegram Bot Notifications** - System alerts and notifications
3. **Terraform Infrastructure** - Infrastructure-as-code for VPS provisioning

## 🚀 Quick Start

### 1. Setup Environment Variables

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` and fill in your values:

```env
# Database Configuration
DB_HOST=db
DB_NAME=hr_connect
DB_USER=arlan
DB_PASS=password

# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
```

### 2. Create Telegram Bot

1. Open Telegram and search for `@BotFather`
2. Send `/newbot` command
3. Follow instructions to create your bot
4. Copy the bot token to `.env`
5. Send a message to your bot
6. Get your chat ID:
   ```bash
   curl https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates
   ```
7. Copy the chat ID to `.env`

### 3. Build and Start Docker Containers

```bash
docker-compose build
docker-compose up -d
```

### 4. Test Backup Script

```bash
# Enter the web container
docker exec -it my_php bash

# Run backup script manually
/var/www/html/scripts/backup_database.sh

# Check backup files
ls -lh /var/backups/hr_connect/

# Check logs
tail -f /var/log/hr_connect_backup.log
```

### 5. Test Telegram Notifications

```bash
# Enter the web container
docker exec -it my_php bash

# Send test notification
php /var/www/html/scripts/telegram_notify.php INFO "Test notification from HR Connect"

# Check logs
tail -f /var/log/hr_connect_telegram.log
```

## 📋 Component Details

### 1. Database Backup Script

**Location:** `scripts/backup_database.sh`

**Features:**
- Automated daily backups at 02:00 AM
- Gzip compression (level 6)
- Timestamp-based filenames
- 30-day retention policy
- Telegram notifications on success/failure
- Error logging

**Manual Execution:**
```bash
docker exec -it my_php /var/www/html/scripts/backup_database.sh
```

**Backup Location:** `/var/backups/hr_connect/`

**Log File:** `/var/log/hr_connect_backup.log`

**Cron Schedule:** Daily at 02:00 AM (configured in `config/crontab`)

### 2. Telegram Bot Notification System

**Location:** `scripts/TelegramNotifier.php`, `scripts/telegram_notify.php`

**Features:**
- Multiple severity levels (INFO, WARNING, ERROR, CRITICAL)
- Retry logic with exponential backoff (1s, 2s, 4s)
- Message deduplication (60-second window)
- Health check endpoint
- Error logging

**Usage:**
```bash
# Send notification
php scripts/telegram_notify.php <SEVERITY> "<MESSAGE>"

# Examples
php scripts/telegram_notify.php INFO "System started successfully"
php scripts/telegram_notify.php WARNING "Disk usage at 80%"
php scripts/telegram_notify.php ERROR "Database connection failed"
php scripts/telegram_notify.php CRITICAL "Server down"
```

**PHP Usage:**
```php
require_once 'scripts/TelegramNotifier.php';

$notifier = new TelegramNotifier();
$notifier->sendNotification('INFO', 'Application deployed successfully');

// Health check
$status = $notifier->healthCheck();
print_r($status);
```

**Log File:** `/var/log/hr_connect_telegram.log`

### 3. Terraform Infrastructure

**Location:** `terraform/`

**Features:**
- VPS provisioning on DigitalOcean
- Automated Docker installation
- Firewall configuration
- SSH key management
- Multiple environment support

**Usage:**

See detailed instructions in [`terraform/README.md`](terraform/README.md)

Quick commands:
```bash
cd terraform
cp terraform.tfvars.example terraform.tfvars
# Edit terraform.tfvars with your values
terraform init
terraform plan
terraform apply
```

## 🔧 Configuration Files

| File | Purpose |
|------|---------|
| `.env` | Environment variables (database, Telegram) |
| `config/crontab` | Cron job schedule |
| `docker-compose.yml` | Docker services configuration |
| `Dockerfile` | Web container image |
| `terraform/main.tf` | Infrastructure definition |
| `terraform/variables.tf` | Terraform variables |
| `terraform/outputs.tf` | Terraform outputs |

## 📊 Monitoring and Logs

### Log Files

| Log File | Purpose |
|----------|---------|
| `/var/log/hr_connect_backup.log` | Backup script logs |
| `/var/log/hr_connect_telegram.log` | Telegram bot logs |

### View Logs

```bash
# Backup logs
docker exec -it my_php tail -f /var/log/hr_connect_backup.log

# Telegram logs
docker exec -it my_php tail -f /var/log/hr_connect_telegram.log

# Docker logs
docker-compose logs -f web
```

### Check Backup Status

```bash
# List all backups
docker exec -it my_php ls -lh /var/backups/hr_connect/

# Check latest backup
docker exec -it my_php ls -lht /var/backups/hr_connect/ | head -n 2

# Check backup size
docker exec -it my_php du -sh /var/backups/hr_connect/
```

## 🧪 Testing

### Test Backup Script

```bash
# Run backup manually
docker exec -it my_php /var/www/html/scripts/backup_database.sh

# Verify backup file created
docker exec -it my_php ls -lh /var/backups/hr_connect/

# Test backup restoration
docker exec -it my_php bash -c "gunzip -c /var/backups/hr_connect/hr_connect_*.sql.gz | mysql -h db -u arlan -ppassword hr_connect_test"
```

### Test Telegram Notifications

```bash
# Test INFO notification
docker exec -it my_php php /var/www/html/scripts/telegram_notify.php INFO "Test INFO message"

# Test WARNING notification
docker exec -it my_php php /var/www/html/scripts/telegram_notify.php WARNING "Test WARNING message"

# Test ERROR notification
docker exec -it my_php php /var/www/html/scripts/telegram_notify.php ERROR "Test ERROR message"

# Test CRITICAL notification
docker exec -it my_php php /var/www/html/scripts/telegram_notify.php CRITICAL "Test CRITICAL message"

# Test health check
docker exec -it my_php php -r "require 'scripts/TelegramNotifier.php'; \$n = new TelegramNotifier(); print_r(\$n->healthCheck());"
```

### Test Terraform

```bash
cd terraform

# Validate configuration
terraform validate

# Check formatting
terraform fmt -check

# Plan (dry-run)
terraform plan

# Apply (requires DigitalOcean account)
terraform apply
```

## 🔒 Security Considerations

1. **Environment Variables**
   - Never commit `.env` file to version control
   - Use strong passwords for database
   - Rotate Telegram bot token regularly

2. **Backup Security**
   - Backup directory has 700 permissions (owner only)
   - Consider encrypting backups with GPG
   - Store backups off-site for disaster recovery

3. **Telegram Bot**
   - Bot token is sensitive - never log or expose it
   - Use environment variables for configuration
   - Implement rate limiting if needed

4. **Terraform**
   - Never commit `terraform.tfvars` to version control
   - Use remote state storage for production
   - Enable 2FA on cloud provider account

## 🐛 Troubleshooting

### Backup Script Issues

**Problem:** Backup fails with "Cannot connect to database"

**Solution:**
```bash
# Check database container is running
docker ps | grep mysql

# Check database credentials in .env
cat .env | grep DB_

# Test database connection
docker exec -it my_php mysql -h db -u arlan -ppassword hr_connect -e "SELECT 1"
```

**Problem:** Cron job not running

**Solution:**
```bash
# Check cron is running in container
docker exec -it my_php ps aux | grep cron

# Check crontab is loaded
docker exec -it my_php crontab -l

# Manually trigger cron
docker exec -it my_php cron
```

### Telegram Bot Issues

**Problem:** "TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID must be set"

**Solution:**
```bash
# Check environment variables are loaded
docker exec -it my_php env | grep TELEGRAM

# Restart container to reload .env
docker-compose restart web
```

**Problem:** Notifications not received

**Solution:**
```bash
# Test bot token
curl https://api.telegram.org/bot<YOUR_TOKEN>/getMe

# Check bot logs
docker exec -it my_php tail -f /var/log/hr_connect_telegram.log

# Test notification manually
docker exec -it my_php php /var/www/html/scripts/telegram_notify.php INFO "Test"
```

### Terraform Issues

**Problem:** "SSH key not found"

**Solution:**
```bash
# List SSH keys in DigitalOcean
doctl compute ssh-key list

# Upload SSH key
doctl compute ssh-key create my-key --public-key "$(cat ~/.ssh/id_rsa.pub)"
```

**Problem:** "Invalid API token"

**Solution:**
```bash
# Create new token at: https://cloud.digitalocean.com/account/api/tokens
# Update terraform.tfvars with new token
```

## 📚 Next Steps

### Priority 2 (Medium) - Coming Soon

- Nginx Reverse Proxy with SSL/HTTPS
- Fail2Ban SSH protection
- n8n workflow automation

### Priority 3 (Complex) - Coming Soon

- Prometheus + Node Exporter (metrics collection)
- Grafana (visualization dashboards)
- Jenkins (CI/CD pipeline)
- Alertmanager (alert routing)

## 📞 Support

For issues or questions:
1. Check the troubleshooting section above
2. Review log files for error messages
3. Check Docker container status: `docker ps`
4. Review environment variables: `docker exec -it my_php env`

## 📝 License

This infrastructure configuration is part of the HR Connect project.
