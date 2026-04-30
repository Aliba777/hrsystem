<?php
/**
 * Telegram Notification System for HR Connect
 * 
 * Description: Sends system alerts and notifications to Telegram
 * Features:
 * - Multiple severity levels (INFO, WARNING, ERROR, CRITICAL)
 * - Retry logic with exponential backoff
 * - Message deduplication
 * - Health check endpoint
 * - Error logging
 * 
 * @author HR Connect Team
 * @version 1.0.0
 */

class TelegramNotifier {
    /**
     * @var string Telegram Bot API token
     */
    private $botToken;
    
    /**
     * @var string Telegram chat ID
     */
    private $chatId;
    
    /**
     * @var string Log file path
     */
    private $logFile = '/var/log/hr_connect_telegram.log';
    
    /**
     * @var int Maximum retry attempts
     */
    private $maxRetries = 3;
    
    /**
     * @var array Message cache for deduplication
     */
    private $lastMessageCache = [];
    
    /**
     * @var int Deduplication window in seconds
     */
    private $deduplicationWindow = 60;
    
    /**
     * Constructor
     * 
     * @throws Exception if required environment variables are not set
     */
    public function __construct() {
        $this->botToken = getenv('TELEGRAM_BOT_TOKEN');
        $this->chatId = getenv('TELEGRAM_CHAT_ID');
        
        if (empty($this->botToken) || empty($this->chatId)) {
            throw new Exception('TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID environment variables must be set');
        }
        
        // Load message cache from file if exists
        $this->loadMessageCache();
    }
    
    /**
     * Send notification to Telegram
     * 
     * @param string $severity Severity level: INFO|WARNING|ERROR|CRITICAL
     * @param string $message Message content
     * @return bool Success status
     */
    public function sendNotification($severity, $message) {
        // Validate severity
        $validSeverities = ['INFO', 'WARNING', 'ERROR', 'CRITICAL'];
        if (!in_array($severity, $validSeverities)) {
            $this->log("ERROR: Invalid severity level: $severity");
            return false;
        }
        
        // Check for duplicate messages (deduplication)
        $messageHash = md5($severity . $message);
        if ($this->isDuplicate($messageHash)) {
            $this->log("INFO: Skipped duplicate message (hash: $messageHash)");
            return true;
        }
        
        $formattedMessage = $this->formatMessage($severity, $message);
        
        // Retry with exponential backoff: 1s, 2s, 4s
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            $this->log("INFO: Sending message (attempt $attempt/$this->maxRetries)...");
            
            $result = $this->sendToTelegram($formattedMessage);
            
            if ($result['success']) {
                $this->log("SUCCESS: Message sent successfully");
                $this->cacheMessage($messageHash);
                $this->saveMessageCache();
                return true;
            }
            
            $this->log("ERROR: Attempt $attempt failed: " . $result['error']);
            
            if ($attempt < $this->maxRetries) {
                $backoffTime = pow(2, $attempt - 1); // 1s, 2s, 4s
                $this->log("INFO: Waiting {$backoffTime}s before retry...");
                sleep($backoffTime);
            }
        }
        
        $this->log("ERROR: Failed to send message after {$this->maxRetries} attempts");
        $this->log("ERROR: Message content: $message");
        return false;
    }
    
    /**
     * Format message with severity emoji and timestamp
     * 
     * @param string $severity Severity level
     * @param string $message Message content
     * @return string Formatted message
     */
    private function formatMessage($severity, $message) {
        $emoji = $this->getSeverityEmoji($severity);
        $timestamp = gmdate('Y-m-d H:i:s') . ' UTC';
        
        // Escape special Markdown characters
        $message = $this->escapeMarkdown($message);
        
        return "{$emoji} *{$severity}*\n\n{$message}\n\n_Time: {$timestamp}_";
    }
    
    /**
     * Get emoji for severity level
     * 
     * @param string $severity Severity level
     * @return string Emoji character
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
     * Escape Markdown special characters
     * 
     * @param string $text Text to escape
     * @return string Escaped text
     */
    private function escapeMarkdown($text) {
        $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
        foreach ($specialChars as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }
        return $text;
    }
    
    /**
     * Send message to Telegram API
     * 
     * @param string $message Formatted message
     * @return array Result with 'success' and 'error' keys
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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode == 200) {
            return ['success' => true, 'error' => null];
        } else {
            $errorMsg = $curlError ?: "HTTP $httpCode";
            if ($response) {
                $responseData = json_decode($response, true);
                if (isset($responseData['description'])) {
                    $errorMsg .= ": " . $responseData['description'];
                }
            }
            return ['success' => false, 'error' => $errorMsg];
        }
    }
    
    /**
     * Check if message is duplicate within deduplication window
     * 
     * @param string $messageHash MD5 hash of message
     * @return bool True if duplicate
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
     * 
     * @param string $messageHash MD5 hash of message
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
     * Load message cache from file
     */
    private function loadMessageCache() {
        $cacheFile = '/tmp/telegram_message_cache.json';
        if (file_exists($cacheFile)) {
            $data = file_get_contents($cacheFile);
            $cache = json_decode($data, true);
            if (is_array($cache)) {
                $this->lastMessageCache = $cache;
            }
        }
    }
    
    /**
     * Save message cache to file
     */
    private function saveMessageCache() {
        $cacheFile = '/tmp/telegram_message_cache.json';
        file_put_contents($cacheFile, json_encode($this->lastMessageCache));
    }
    
    /**
     * Log message to file
     * 
     * @param string $message Message to log
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message\n";
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Health check - returns bot status
     * 
     * @return array Status information
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
                'bot_id' => $data['result']['id'] ?? 'unknown',
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
