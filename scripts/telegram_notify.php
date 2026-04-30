<?php
/**
 * CLI Wrapper for Telegram Notifications
 * 
 * Usage: php telegram_notify.php <severity> <message>
 * 
 * Severity levels: INFO, WARNING, ERROR, CRITICAL
 * 
 * Examples:
 *   php telegram_notify.php INFO "System started successfully"
 *   php telegram_notify.php ERROR "Database connection failed"
 *   php telegram_notify.php CRITICAL "Disk space critical: 95% full"
 * 
 * Exit codes:
 *   0 - Success
 *   1 - Error (invalid arguments, notification failed, etc.)
 */

require_once __DIR__ . '/TelegramNotifier.php';

// Check command-line arguments
if ($argc < 3) {
    echo "Usage: php telegram_notify.php <severity> <message>\n";
    echo "\n";
    echo "Severity levels:\n";
    echo "  INFO     - Informational messages\n";
    echo "  WARNING  - Warning messages\n";
    echo "  ERROR    - Error messages\n";
    echo "  CRITICAL - Critical alerts\n";
    echo "\n";
    echo "Example:\n";
    echo "  php telegram_notify.php INFO \"Backup completed successfully\"\n";
    echo "\n";
    exit(1);
}

$severity = strtoupper($argv[1]);
$message = $argv[2];

// Validate severity
$validSeverities = ['INFO', 'WARNING', 'ERROR', 'CRITICAL'];
if (!in_array($severity, $validSeverities)) {
    echo "Error: Invalid severity level '$severity'\n";
    echo "Valid levels: " . implode(', ', $validSeverities) . "\n";
    exit(1);
}

// Send notification
try {
    $notifier = new TelegramNotifier();
    $success = $notifier->sendNotification($severity, $message);
    
    if ($success) {
        echo "Notification sent successfully\n";
        exit(0);
    } else {
        echo "Failed to send notification\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
