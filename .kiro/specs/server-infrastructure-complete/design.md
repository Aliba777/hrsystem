# Design Document: Server Infrastructure Complete - Priority 1 (Easy Components)

## Overview

This design document specifies the technical implementation for Priority 1 (Easy) components of the HR Connect server infrastructure:

1. **Database Backup Script** - Automated MySQL backup with cron scheduling
2. **Telegram Bot Notification System** - PHP class for sending system alerts
3. **Terraform Infrastructure Module** - Basic IaC configuration for VPS provisioning

These components provide foundational infrastructure capabilities with minimal complexity and quick implementation time.

---

## Component 1: Database Backup Script

### High-Level Design

**Architecture:**
```
┌─────────────────┐
│  Cron Scheduler │
│   (02:00 AM)    │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────┐
│   backup_database.sh            │
│                                 │
│  1. Check DB connectivity       │
│  2. Create backup directory     │
│  3. Run mysqldump               │
│  4. Compress with gzip          │
│  5. Delete old backups (>30d)   │
│  6. Send Telegram notification  │
│  7. Log result                  │
└────────┬────────────────────────┘
         │
         ▼
┌─────────────────────────────────┐
│  /var/backups/hr_connect/       │
│  ├── hr_connect_20260430_020000.sql.gz
│  ├── hr_connect_20260429_020000.sql.gz
│  └── ...                        │
└─────────────────────────────────┘
```

### Low-Level Design

**File:** `scripts/backup_database.sh`

```bash
#!/bin/bash
# Database Backup Script for HR Connect
# Runs daily at 02:00 AM via cron
# Usage: ./backup_database.sh

# Configuration
DB_HOST="${DB_HOST:-db}"
DB_NAME="${DB_NAME:-hr_connect}"
DB_USER="${DB_USER:-arlan}"
DB_PASS="${DB_PASS:-password}"
BACKUP_DIR="/var/backups/hr_connect"
LOG_FILE="/var/log/hr_connect_backup.log"
RETENTION_DAYS=30
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="hr_connect_${TIMESTAMP}.sql.gz"

# Function: log_message
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Function: send_telegram_notification
send_telegram_notification() {
    local message="$1"
    local severity="$2"
    php /var/www/html/scripts/telegram_notify.php "$severity" "$message"
}

# Main execution
log_message "Starting database backup..."

# 1. Create backup directory if not exists
if [ ! -d "$BACKUP_DIR" ]; then
    mkdir -p "$BACKUP_DIR"
    chmod 700 "$BACKUP_DIR"
    log_message "Created backup directory: $BACKUP_DIR"
fi

# 2. Test database connectivity
if ! mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1" &>/dev/null; then
    log_message "ERROR: Cannot connect to database"
    send_telegram_notification "Database backup failed: Cannot connect to database" "ERROR"
    exit 1
fi

# 3. Perform mysqldump and compress
if mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip -6 > "$BACKUP_DIR/$BACKUP_FILE"; then
    BACKUP_SIZE=$(du -h "$BACKUP_DIR/$BACKUP_FILE" | cut -f1)
    log_message "SUCCESS: Backup created: $BACKUP_FILE (Size: $BACKUP_SIZE)"
    send_telegram_notification "✅ Database backup successful: $BACKUP_FILE ($BACKUP_SIZE)" "INFO"
else
    log_message "ERROR: Backup failed"
    send_telegram_notification "❌ Database backup failed" "ERROR"
    exit 1
fi

# 4. Delete old backups (older than RETENTION_DAYS)
find "$BACKUP_DIR" -name "hr_connect_*.sql.gz" -type f -mtime +$RETENTION_DAYS -delete
log_message "Cleaned up backups older than $RETENTION_DAYS days"

log_message "Backup completed successfully"
exit 0
```

**Cron Configuration:**

File: `config/crontab`

```cron
# HR Connect Database Backup - Daily at 02:00 AM
0 2 * * * /var/www/html/scripts/backup_database.sh >> /var/log/hr_connect_backup.log 2>&1
```

**Docker Integration:**

Add to `docker-compose.yml`:

```yaml
  web:
    build: .
    container_name: my_php
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www/html/
      - backup_data:/var/backups/hr_connect
    depends_on:
      - db
    # Add cron job on container start
    command: >
      sh -c "crontab /var/www/html/config/crontab && apache2-foreground"

volumes:
  db_data:
  backup_data:
```

---

## Component 2: Telegram Bot Notification System

### High-Level Design

**Architecture:**
```
┌──────────────────────┐
│  Application Layer   │
│  (Backup Script,     │
│   Monitoring, etc.)  │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────────────┐
│  TelegramNotifier.php        │
│                              │
│  - sendNotification()        │
│  - formatMessage()           │
│  - retryWithBackoff()        │
│  - logFailure()              │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│  Telegram Bot API            │
│  https://api.telegram.org    │
└──────────────────────────────┘
```

### Low-Level Design

**File:** `scripts/TelegramNotifier.php`

```php
<?php
/**
 * Telegram Notification System for HR Connect
 * Sends system alerts and notifications to Telegram
 */

class TelegramNotifier {
    private $botToken;
    private $chatId;
    private $logFile = '/var/log/hr_connect_telegram.log';
    private $maxRetries = 3;
    private $lastMessageCache = [];
    private $deduplicationWindow = 60; // seconds
    
    public function __construct() {
        $this->botToken = getenv('TELEGRAM_BOT_TOKEN');
        $this->chatId = getenv('TELEGRAM_CHAT_ID');
        
        if (empty($this->botToken) || empty($this->chatId)) {
            throw new Exception('TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID must be set');
        }
    }
    
    /**
     * Send notification to Telegram
     * @param string $severity INFO|WARNING|ERROR|CRITICAL
     * @param string $message Message content
     * @return bool Success status
     */
    public function sendNotification($severity, $message) {
        // Check for duplicate messages (deduplication)
        $messageHash = md5($severity . $message);
        if ($this->isDuplicate($messageHash)) {
            $this->log("Skipped duplicate message: $message");
            return true;
        }
        
        $formattedMessage = $this->formatMessage($severity, $message);
        
        // Retry with exponential backoff
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            $result = $this->sendToTelegram($formattedMessage);
            
            if ($result['success']) {
                $this->log("Message sent successfully: $message");
                $this->cacheMessage($messageHash);
                return true;
            }
            
            if ($attempt < $this->maxRetries) {
                $backoffTime = pow(2, $attempt - 1); // 1s, 2s, 4s
                $this->log("Retry attempt $attempt failed, waiting {$backoffTime}s...");
                sleep($backoffTime);
            }
        }
        
        $this->log("ERROR: Failed to send message after {$this->maxRetries} attempts: $message");
        return false;
    }
    
    /**
     * Format message with severity and timestamp
     */
    private function formatMessage($severity, $message) {
        $emoji = $this->getSeverityEmoji($severity);
        $timestamp = gmdate('Y-m-d H:i:s') . ' UTC';
        
        return "{$emoji} *{$severity}*\n\n{$message}\n\n_Time: {$timestamp}_";
    }
    
    /**
     * Get emoji for severity level
     */
    private function getSeverityEmoji($severity) {
        $emojis = [
            'INFO' => 'ℹ️',
            'WARNING' => '⚠️',
            'ERROR' => '❌',
            'CRITICAL' => '🚨'
        ];
        return $emojis[$severity] ?? 'ℹ️';
    }
    
    /**
     * Send message to Telegram API
     */
    private function sendToTelegram($message) {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        
        $data = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode == 200) {
            return ['success' => true];
        } else {
            return [
                'success' => false,
                'error' => $error ?: "HTTP $httpCode",
                'response' => $response
            ];
        }
    }
    
    /**
     * Check if message is duplicate within deduplication window
     */
    private function isDuplicate($messageHash) {
        $now = time();
        if (isset($this->lastMessageCache[$messageHash])) {
            $lastSent = $this->lastMessageCache[$messageHash];
            if (($now - $lastSent) < $this->deduplicationWindow) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Cache message hash with timestamp
     */
    private function cacheMessage($messageHash) {
        $this->lastMessageCache[$messageHash] = time();
        
        // Clean old cache entries
        $now = time();
        foreach ($this->lastMessageCache as $hash => $timestamp) {
            if (($now - $timestamp) > $this->deduplicationWindow) {
                unset($this->lastMessageCache[$hash]);
            }
        }
    }
    
    /**
     * Log message to file
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
    
    /**
     * Health check - returns bot status
     */
    public function healthCheck() {
        $url = "https://api.telegram.org/bot{$this->botToken}/getMe";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            $data = json_decode($response, true);
            return [
                'status' => 'healthy',
                'bot_name' => $data['result']['username'] ?? 'unknown',
                'last_check' => gmdate('Y-m-d H:i:s') . ' UTC'
            ];
        } else {
            return [
                'status' => 'unhealthy',
                'error' => "HTTP $httpCode",
                'last_check' => gmdate('Y-m-d H:i:s') . ' UTC'
            ];
        }
    }
}
?>
```

**CLI Wrapper:** `scripts/telegram_notify.php`

```php
<?php
/**
 * CLI wrapper for Telegram notifications
 * Usage: php telegram_notify.php <severity> <message>
 */

require_once __DIR__ . '/TelegramNotifier.php';

if ($argc < 3) {
    echo "Usage: php telegram_notify.php <severity> <message>\n";
    echo "Severity: INFO|WARNING|ERROR|CRITICAL\n";
    exit(1);
}

$severity = $argv[1];
$message = $argv[2];

try {
    $notifier = new TelegramNotifier();
    $success = $notifier->sendNotification($severity, $message);
    exit($success ? 0 : 1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
```

**Environment Configuration:**

Add to `.env` file:

```env
# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
```

Add to `docker-compose.yml`:

```yaml
  web:
    build: .
    container_name: my_php
    env_file:
      - .env
    environment:
      - TELEGRAM_BOT_TOKEN=${TELEGRAM_BOT_TOKEN}
      - TELEGRAM_CHAT_ID=${TELEGRAM_CHAT_ID}
```

---

## Component 3: Terraform Infrastructure Module

### High-Level Design

**Architecture:**
```
┌─────────────────────────────┐
│  Terraform Configuration    │
│  (main.tf, variables.tf,    │
│   outputs.tf)               │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│  Cloud Provider API         │
│  (DigitalOcean/AWS/Hetzner) │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│  Provisioned VPS Instance   │
│  - Ubuntu 22.04 LTS         │
│  - Firewall rules           │
│  - SSH key access           │
│  - Public IP                │
└─────────────────────────────┘
```

### Low-Level Design

**File:** `terraform/main.tf`

```hcl
# Terraform configuration for HR Connect infrastructure
# Provider: DigitalOcean (can be adapted for AWS/Hetzner)

terraform {
  required_version = ">= 1.0"
  
  required_providers {
    digitalocean = {
      source  = "digitalocean/digitalocean"
      version = "~> 2.0"
    }
  }
  
  # Remote state storage (optional - uncomment for production)
  # backend "s3" {
  #   bucket = "hr-connect-terraform-state"
  #   key    = "infrastructure/terraform.tfstate"
  #   region = "us-east-1"
  # }
}

# Configure DigitalOcean provider
provider "digitalocean" {
  token = var.do_token
}

# Create VPS instance (Droplet)
resource "digitalocean_droplet" "hr_connect_server" {
  name   = "${var.environment_name}-hr-connect"
  region = var.region
  size   = var.instance_type
  image  = "ubuntu-22-04-x64"
  
  # SSH key for access
  ssh_keys = [
    data.digitalocean_ssh_key.main.id
  ]
  
  # Tags for organization
  tags = [
    "environment:${var.environment_name}",
    "project:hr-connect",
    "managed-by:terraform"
  ]
  
  # User data script for initial setup
  user_data = <<-EOF
    #!/bin/bash
    apt-get update
    apt-get install -y docker.io docker-compose git
    systemctl enable docker
    systemctl start docker
    
    # Create application directory
    mkdir -p /opt/hr-connect
    chown -R root:root /opt/hr-connect
  EOF
}

# Lookup existing SSH key
data "digitalocean_ssh_key" "main" {
  name = var.ssh_key_name
}

# Create firewall rules
resource "digitalocean_firewall" "hr_connect_firewall" {
  name = "${var.environment_name}-hr-connect-firewall"
  
  droplet_ids = [digitalocean_droplet.hr_connect_server.id]
  
  # SSH access
  inbound_rule {
    protocol         = "tcp"
    port_range       = "22"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  # HTTP access
  inbound_rule {
    protocol         = "tcp"
    port_range       = "80"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  # HTTPS access
  inbound_rule {
    protocol         = "tcp"
    port_range       = "443"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  # Web application (Docker)
  inbound_rule {
    protocol         = "tcp"
    port_range       = "8080"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  # phpMyAdmin (Docker)
  inbound_rule {
    protocol         = "tcp"
    port_range       = "8081"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  # Allow all outbound traffic
  outbound_rule {
    protocol              = "tcp"
    port_range            = "1-65535"
    destination_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  outbound_rule {
    protocol              = "udp"
    port_range            = "1-65535"
    destination_addresses = ["0.0.0.0/0", "::/0"]
  }
}
```

**File:** `terraform/variables.tf`

```hcl
# Terraform variables for HR Connect infrastructure

variable "do_token" {
  description = "DigitalOcean API token"
  type        = string
  sensitive   = true
}

variable "environment_name" {
  description = "Environment name (e.g., dev, staging, production)"
  type        = string
  default     = "production"
}

variable "region" {
  description = "DigitalOcean region"
  type        = string
  default     = "fra1" # Frankfurt
  
  validation {
    condition     = contains(["nyc1", "nyc3", "sfo3", "fra1", "lon1", "sgp1"], var.region)
    error_message = "Region must be a valid DigitalOcean region."
  }
}

variable "instance_type" {
  description = "Droplet size/type"
  type        = string
  default     = "s-2vcpu-4gb" # 2 vCPU, 4GB RAM
  
  validation {
    condition     = can(regex("^s-", var.instance_type))
    error_message = "Instance type must be a valid DigitalOcean droplet size."
  }
}

variable "ssh_key_name" {
  description = "Name of existing SSH key in DigitalOcean"
  type        = string
}
```

**File:** `terraform/outputs.tf`

```hcl
# Terraform outputs for HR Connect infrastructure

output "instance_id" {
  description = "ID of the created droplet"
  value       = digitalocean_droplet.hr_connect_server.id
}

output "instance_public_ip" {
  description = "Public IP address of the server"
  value       = digitalocean_droplet.hr_connect_server.ipv4_address
}

output "instance_name" {
  description = "Name of the server"
  value       = digitalocean_droplet.hr_connect_server.name
}

output "firewall_id" {
  description = "ID of the firewall"
  value       = digitalocean_firewall.hr_connect_firewall.id
}

output "ssh_connection_string" {
  description = "SSH connection command"
  value       = "ssh root@${digitalocean_droplet.hr_connect_server.ipv4_address}"
}
```

**File:** `terraform/terraform.tfvars.example`

```hcl
# Example Terraform variables file
# Copy to terraform.tfvars and fill in your values

do_token         = "your_digitalocean_api_token_here"
environment_name = "production"
region           = "fra1"
instance_type    = "s-2vcpu-4gb"
ssh_key_name     = "your_ssh_key_name"
```

**File:** `terraform/README.md`

```markdown
# HR Connect Terraform Infrastructure

This Terraform configuration provisions a VPS instance for the HR Connect platform on DigitalOcean.

## Prerequisites

- Terraform >= 1.0
- DigitalOcean account and API token
- SSH key uploaded to DigitalOcean

## Usage

1. Copy the example variables file:
   ```bash
   cp terraform.tfvars.example terraform.tfvars
   ```

2. Edit `terraform.tfvars` with your values:
   - `do_token`: Your DigitalOcean API token
   - `ssh_key_name`: Name of your SSH key in DigitalOcean
   - `region`: Desired region (default: fra1)
   - `instance_type`: Droplet size (default: s-2vcpu-4gb)

3. Initialize Terraform:
   ```bash
   terraform init
   ```

4. Preview changes:
   ```bash
   terraform plan
   ```

5. Apply configuration:
   ```bash
   terraform apply
   ```

6. Get outputs:
   ```bash
   terraform output
   ```

7. Destroy infrastructure (when needed):
   ```bash
   terraform destroy
   ```

## Outputs

- `instance_public_ip`: Public IP address of the server
- `instance_id`: DigitalOcean droplet ID
- `ssh_connection_string`: SSH command to connect to the server

## Firewall Rules

The following ports are open:
- 22 (SSH)
- 80 (HTTP)
- 443 (HTTPS)
- 8080 (Web application)
- 8081 (phpMyAdmin)

## Adapting for Other Providers

To use AWS instead of DigitalOcean, replace the provider block in `main.tf`:

```hcl
provider "aws" {
  region = var.region
}

resource "aws_instance" "hr_connect_server" {
  ami           = "ami-0c55b159cbfafe1f0" # Ubuntu 22.04
  instance_type = var.instance_type
  key_name      = var.ssh_key_name
  
  tags = {
    Name        = "${var.environment_name}-hr-connect"
    Environment = var.environment_name
  }
}
```
```

---

## Implementation Plan

### Phase 1: Database Backup Script (Day 1)
1. Create `scripts/backup_database.sh`
2. Create `scripts/TelegramNotifier.php`
3. Create `scripts/telegram_notify.php`
4. Add `.env` configuration
5. Update `docker-compose.yml` with volumes and cron
6. Test backup script manually
7. Verify Telegram notifications
8. Test cron scheduling

### Phase 2: Telegram Bot System (Day 1-2)
1. Create Telegram bot via @BotFather
2. Get bot token and chat ID
3. Configure environment variables
4. Test notification sending
5. Test retry logic with exponential backoff
6. Test deduplication
7. Test health check endpoint

### Phase 3: Terraform Module (Day 2)
1. Create `terraform/` directory structure
2. Create `main.tf`, `variables.tf`, `outputs.tf`
3. Create `terraform.tfvars.example`
4. Create `README.md` with usage instructions
5. Test `terraform plan`
6. Test `terraform apply` (optional - requires cloud account)
7. Document provider alternatives (AWS, Hetzner)

---

## Testing Strategy

### Unit Tests

**Backup Script Tests:**
- Test database connectivity check
- Test backup file creation
- Test compression
- Test old backup deletion
- Test error handling

**Telegram Bot Tests:**
- Test message formatting
- Test retry logic
- Test deduplication
- Test health check
- Test error logging

**Terraform Tests:**
- Test `terraform validate`
- Test `terraform plan` output
- Test variable validation
- Test output values

### Integration Tests

- End-to-end backup with Telegram notification
- Backup restoration (round-trip test)
- Terraform apply and destroy cycle

---

## Security Considerations

1. **Backup Script:**
   - Store database credentials in environment variables
   - Set backup directory permissions to 700
   - Consider GPG encryption for backups

2. **Telegram Bot:**
   - Never log bot token
   - Use environment variables for sensitive data
   - Implement rate limiting

3. **Terraform:**
   - Use remote state storage for production
   - Never commit `terraform.tfvars` to version control
   - Use separate workspaces for environments

---

## Monitoring and Logging

- Backup script logs to `/var/log/hr_connect_backup.log`
- Telegram bot logs to `/var/log/hr_connect_telegram.log`
- Terraform state tracked in `terraform.tfstate`
- All logs rotated daily with logrotate

---

## Documentation

- Inline comments in all scripts
- README files for each component
- Usage examples in documentation
- Troubleshooting guide for common issues
