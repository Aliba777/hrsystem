# Tasks: Server Infrastructure Complete - Priority 1 (Easy Components)

## Task 1: Database Backup Script Implementation

### Task 1.1: Create backup script
- [ ] Create `scripts/` directory if not exists
- [ ] Create `scripts/backup_database.sh` with executable permissions
- [ ] Implement database connectivity check
- [ ] Implement mysqldump with gzip compression
- [ ] Implement timestamp-based filename generation
- [ ] Implement old backup cleanup (30-day retention)
- [ ] Implement error logging to `/var/log/hr_connect_backup.log`
- [ ] Add inline comments and usage instructions

### Task 1.2: Configure cron scheduling
- [ ] Create `config/crontab` file
- [ ] Add daily backup job at 02:00 AM
- [ ] Update `Dockerfile` to install cron
- [ ] Update `docker-compose.yml` to run cron on container start
- [ ] Add `backup_data` volume to docker-compose.yml

### Task 1.3: Test backup script
- [ ] Test manual execution of backup script
- [ ] Verify backup file creation with correct naming
- [ ] Verify gzip compression works
- [ ] Test database connectivity error handling
- [ ] Test old backup deletion (create test files with old timestamps)
- [ ] Verify log file creation and content

---

## Task 2: Telegram Bot Notification System

### Task 2.1: Create Telegram bot
- [ ] Create bot via @BotFather on Telegram
- [ ] Get bot token
- [ ] Get chat ID (send message to bot and use getUpdates API)
- [ ] Document bot creation process in README

### Task 2.2: Implement TelegramNotifier class
- [ ] Create `scripts/TelegramNotifier.php`
- [ ] Implement `sendNotification()` method
- [ ] Implement `formatMessage()` with severity emojis
- [ ] Implement retry logic with exponential backoff (1s, 2s, 4s)
- [ ] Implement message deduplication (60-second window)
- [ ] Implement error logging to `/var/log/hr_connect_telegram.log`
- [ ] Implement `healthCheck()` method
- [ ] Add inline comments and PHPDoc

### Task 2.3: Create CLI wrapper
- [ ] Create `scripts/telegram_notify.php`
- [ ] Implement command-line argument parsing
- [ ] Add usage instructions
- [ ] Test CLI execution

### Task 2.4: Configure environment variables
- [ ] Create `.env.example` file with Telegram configuration
- [ ] Add `TELEGRAM_BOT_TOKEN` variable
- [ ] Add `TELEGRAM_CHAT_ID` variable
- [ ] Update `docker-compose.yml` to load `.env` file
- [ ] Add `.env` to `.gitignore`

### Task 2.5: Integrate with backup script
- [ ] Update `backup_database.sh` to call `telegram_notify.php`
- [ ] Send success notification with backup filename and size
- [ ] Send error notification on backup failure
- [ ] Test end-to-end backup with Telegram notification

### Task 2.6: Test Telegram bot
- [ ] Test sending INFO notification
- [ ] Test sending WARNING notification
- [ ] Test sending ERROR notification
- [ ] Test sending CRITICAL notification
- [ ] Test retry logic (disconnect network temporarily)
- [ ] Test message deduplication (send same message twice)
- [ ] Test health check endpoint
- [ ] Verify error logging

---

## Task 3: Terraform Infrastructure Module

### Task 3.1: Create Terraform directory structure
- [ ] Create `terraform/` directory
- [ ] Create `terraform/main.tf`
- [ ] Create `terraform/variables.tf`
- [ ] Create `terraform/outputs.tf`
- [ ] Create `terraform/terraform.tfvars.example`
- [ ] Create `terraform/README.md`
- [ ] Create `terraform/.gitignore` (ignore `*.tfvars`, `*.tfstate`)

### Task 3.2: Implement main.tf
- [ ] Configure Terraform version requirement
- [ ] Configure DigitalOcean provider
- [ ] Define VPS instance resource (droplet)
- [ ] Add SSH key data source
- [ ] Add user_data script for Docker installation
- [ ] Define firewall resource with rules (22, 80, 443, 8080, 8081)
- [ ] Add resource tags
- [ ] Add inline comments

### Task 3.3: Implement variables.tf
- [ ] Define `do_token` variable (sensitive)
- [ ] Define `environment_name` variable
- [ ] Define `region` variable with validation
- [ ] Define `instance_type` variable with validation
- [ ] Define `ssh_key_name` variable
- [ ] Add descriptions for all variables

### Task 3.4: Implement outputs.tf
- [ ] Define `instance_id` output
- [ ] Define `instance_public_ip` output
- [ ] Define `instance_name` output
- [ ] Define `firewall_id` output
- [ ] Define `ssh_connection_string` output
- [ ] Add descriptions for all outputs

### Task 3.5: Create documentation
- [ ] Write `terraform/README.md` with:
  - Prerequisites
  - Usage instructions
  - Variable descriptions
  - Output descriptions
  - Firewall rules documentation
  - Provider alternatives (AWS, Hetzner)
- [ ] Create `terraform.tfvars.example` with sample values

### Task 3.6: Test Terraform configuration
- [ ] Run `terraform init`
- [ ] Run `terraform validate`
- [ ] Run `terraform plan` (requires DigitalOcean token)
- [ ] Verify plan output shows correct resources
- [ ] (Optional) Run `terraform apply` to provision infrastructure
- [ ] (Optional) Run `terraform destroy` to clean up

---

## Task 4: Documentation and Configuration

### Task 4.1: Update project README
- [ ] Add section for "Infrastructure Components"
- [ ] Document database backup system
- [ ] Document Telegram notification system
- [ ] Document Terraform infrastructure
- [ ] Add setup instructions for each component

### Task 4.2: Create troubleshooting guide
- [ ] Document common backup script issues
- [ ] Document Telegram bot connection issues
- [ ] Document Terraform authentication issues
- [ ] Add solutions for each issue

### Task 4.3: Update Ansible playbook
- [ ] Add task to deploy backup script
- [ ] Add task to configure cron
- [ ] Add task to deploy Telegram notifier
- [ ] Add task to set environment variables

---

## Task 5: Testing and Validation

### Task 5.1: End-to-end testing
- [ ] Test complete backup workflow with Telegram notification
- [ ] Test backup restoration (round-trip test)
- [ ] Test cron scheduling (wait for scheduled execution or adjust time)
- [ ] Test Terraform plan and validate

### Task 5.2: Error scenario testing
- [ ] Test backup with invalid database credentials
- [ ] Test backup with full disk
- [ ] Test Telegram notification with invalid token
- [ ] Test Telegram notification with network disconnection
- [ ] Test Terraform with invalid cloud credentials

### Task 5.3: Performance testing
- [ ] Measure backup script execution time
- [ ] Measure backup file size
- [ ] Measure Telegram notification latency
- [ ] Verify backup script resource usage (CPU, memory)

---

## Acceptance Criteria

### Database Backup Script
- ✅ Backup script creates compressed backup with timestamp
- ✅ Cron executes backup daily at 02:00 AM
- ✅ Old backups (>30 days) are automatically deleted
- ✅ Errors are logged to `/var/log/hr_connect_backup.log`
- ✅ Telegram notification sent on success and failure

### Telegram Bot
- ✅ Bot sends notifications with severity levels (INFO, WARNING, ERROR, CRITICAL)
- ✅ Retry logic with exponential backoff (1s, 2s, 4s)
- ✅ Message deduplication within 60-second window
- ✅ Health check endpoint returns bot status
- ✅ Configuration via environment variables

### Terraform Module
- ✅ `terraform plan` shows correct resources
- ✅ `terraform validate` passes without errors
- ✅ Variables have validation rules
- ✅ Outputs display instance IP and connection string
- ✅ Firewall rules configured for ports 22, 80, 443, 8080, 8081
- ✅ README includes usage instructions and examples

---

## Estimated Time

- Task 1 (Backup Script): 2-3 hours
- Task 2 (Telegram Bot): 3-4 hours
- Task 3 (Terraform): 2-3 hours
- Task 4 (Documentation): 1-2 hours
- Task 5 (Testing): 2-3 hours

**Total: 10-15 hours (1-2 days)**
