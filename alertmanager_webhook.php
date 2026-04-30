<?php
/**
 * Alertmanager Webhook Handler for HR Connect
 * Receives alerts from Alertmanager and sends them to Telegram
 */

require_once __DIR__ . '/scripts/TelegramNotifier.php';

// Read JSON payload from Alertmanager
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Log webhook received
error_log("[Alertmanager Webhook] Received: " . $json);

if (!$data || !isset($data['alerts'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

try {
    $notifier = new TelegramNotifier();
    
    foreach ($data['alerts'] as $alert) {
        $status = $alert['status'] ?? 'unknown';
        $labels = $alert['labels'] ?? [];
        $annotations = $alert['annotations'] ?? [];
        
        $alertname = $labels['alertname'] ?? 'Unknown Alert';
        $severity = strtoupper($labels['severity'] ?? 'info');
        $instance = $labels['instance'] ?? 'unknown';
        $summary = $annotations['summary'] ?? '';
        $description = $annotations['description'] ?? '';
        
        // Format message
        if ($status === 'firing') {
            $message = "🔥 ALERT FIRING\n\n";
            $message .= "Alert: {$alertname}\n";
            $message .= "Instance: {$instance}\n";
            if ($summary) $message .= "Summary: {$summary}\n";
            if ($description) $message .= "Description: {$description}\n";
        } else if ($status === 'resolved') {
            $message = "✅ ALERT RESOLVED\n\n";
            $message .= "Alert: {$alertname}\n";
            $message .= "Instance: {$instance}\n";
            $severity = 'INFO';
        } else {
            continue;
        }
        
        // Send to Telegram
        $notifier->sendNotification($severity, $message);
    }
    
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    error_log("[Alertmanager Webhook] Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
