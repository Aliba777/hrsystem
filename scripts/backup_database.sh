#!/bin/bash
###############################################################################
# Database Backup Script for HR Connect
# 
# Description: Automated MySQL database backup with compression and retention
# Schedule: Runs daily at 02:00 AM via cron
# Usage: ./backup_database.sh
#
# Features:
# - Creates compressed mysqldump backups
# - Timestamp-based filenames
# - 30-day retention policy
# - Telegram notifications
# - Error logging
###############################################################################

# Configuration from environment variables (with defaults)
DB_HOST="${DB_HOST:-db}"
DB_NAME="${DB_NAME:-hr_connect}"
DB_USER="${DB_USER:-arlan}"
DB_PASS="${DB_PASS:-password}"
BACKUP_DIR="/var/backups/hr_connect"
LOG_FILE="/var/log/hr_connect_backup.log"
RETENTION_DAYS=30
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="hr_connect_${TIMESTAMP}.sql.gz"

###############################################################################
# Function: log_message
# Description: Logs message with timestamp to log file and stdout
# Arguments: $1 - Message to log
###############################################################################
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

###############################################################################
# Function: send_telegram_notification
# Description: Sends notification via Telegram bot
# Arguments: $1 - Message, $2 - Severity (INFO|WARNING|ERROR|CRITICAL)
###############################################################################
send_telegram_notification() {
    local message="$1"
    local severity="$2"
    
    # Check if telegram_notify.php exists
    if [ -f "/var/www/html/scripts/telegram_notify.php" ]; then
        php /var/www/html/scripts/telegram_notify.php "$severity" "$message" 2>&1 | tee -a "$LOG_FILE"
    else
        log_message "WARNING: telegram_notify.php not found, skipping notification"
    fi
}

###############################################################################
# Main Execution
###############################################################################

log_message "========================================="
log_message "Starting database backup process..."
log_message "========================================="

# Step 1: Create backup directory if it doesn't exist
if [ ! -d "$BACKUP_DIR" ]; then
    log_message "Creating backup directory: $BACKUP_DIR"
    mkdir -p "$BACKUP_DIR"
    chmod 700 "$BACKUP_DIR"
    
    if [ $? -eq 0 ]; then
        log_message "✓ Backup directory created successfully"
    else
        log_message "✗ ERROR: Failed to create backup directory"
        send_telegram_notification "Database backup failed: Cannot create backup directory" "ERROR"
        exit 1
    fi
else
    log_message "✓ Backup directory exists: $BACKUP_DIR"
fi

# Step 2: Test database connectivity
log_message "Testing database connectivity..."
if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" --skip-ssl -e "SELECT 1" "$DB_NAME" &>/dev/null; then
    log_message "✓ Database connection successful"
else
    log_message "✗ ERROR: Cannot connect to database"
    log_message "  Host: $DB_HOST"
    log_message "  Database: $DB_NAME"
    log_message "  User: $DB_USER"
    send_telegram_notification "❌ Database backup failed: Cannot connect to database $DB_NAME@$DB_HOST" "ERROR"
    exit 1
fi

# Step 3: Perform mysqldump and compress with gzip
log_message "Creating database backup..."
log_message "  File: $BACKUP_FILE"

START_TIME=$(date +%s)

if mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" --skip-ssl \
    --single-transaction \
    --quick \
    --lock-tables=false \
    "$DB_NAME" | gzip -6 > "$BACKUP_DIR/$BACKUP_FILE"; then
    
    END_TIME=$(date +%s)
    DURATION=$((END_TIME - START_TIME))
    BACKUP_SIZE=$(du -h "$BACKUP_DIR/$BACKUP_FILE" | cut -f1)
    
    log_message "✓ Backup created successfully"
    log_message "  Size: $BACKUP_SIZE"
    log_message "  Duration: ${DURATION}s"
    
    # Verify backup file is not empty
    if [ -s "$BACKUP_DIR/$BACKUP_FILE" ]; then
        log_message "✓ Backup file verification passed"
        send_telegram_notification "✅ Database backup successful\n\nFile: $BACKUP_FILE\nSize: $BACKUP_SIZE\nDuration: ${DURATION}s" "INFO"
    else
        log_message "✗ ERROR: Backup file is empty"
        rm -f "$BACKUP_DIR/$BACKUP_FILE"
        send_telegram_notification "❌ Database backup failed: Backup file is empty" "ERROR"
        exit 1
    fi
else
    log_message "✗ ERROR: Backup failed during mysqldump"
    send_telegram_notification "❌ Database backup failed: mysqldump error" "ERROR"
    exit 1
fi

# Step 4: Delete old backups (older than RETENTION_DAYS)
log_message "Cleaning up old backups (retention: ${RETENTION_DAYS} days)..."

OLD_BACKUPS=$(find "$BACKUP_DIR" -name "hr_connect_*.sql.gz" -type f -mtime +$RETENTION_DAYS)
OLD_COUNT=$(echo "$OLD_BACKUPS" | grep -c "hr_connect_" || echo "0")

if [ "$OLD_COUNT" -gt 0 ]; then
    log_message "  Found $OLD_COUNT old backup(s) to delete"
    find "$BACKUP_DIR" -name "hr_connect_*.sql.gz" -type f -mtime +$RETENTION_DAYS -delete
    log_message "✓ Old backups deleted"
else
    log_message "  No old backups to delete"
fi

# Step 5: Display backup statistics
TOTAL_BACKUPS=$(find "$BACKUP_DIR" -name "hr_connect_*.sql.gz" -type f | wc -l)
TOTAL_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)

log_message "========================================="
log_message "Backup Statistics:"
log_message "  Total backups: $TOTAL_BACKUPS"
log_message "  Total size: $TOTAL_SIZE"
log_message "  Latest backup: $BACKUP_FILE ($BACKUP_SIZE)"
log_message "========================================="
log_message "Backup process completed successfully"
log_message "========================================="

exit 0
