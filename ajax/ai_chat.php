<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'response' => 'Авторизация қажет']);
    exit;
}

require_once '../ai_assistant/init.php';

$message = $_POST['message'] ?? '';
$context = $_POST['context'] ?? 'general';

if (empty($message)) {
    echo json_encode(['success' => false, 'response' => 'Хабарлама бос']);
    exit;
}

$ai = new AIAssistant($_SESSION['user_id'], $_SESSION['user_type']);
$result = $ai->sendMessage($message, $context);

echo json_encode($result);
?>