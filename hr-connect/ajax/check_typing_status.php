<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'is_typing' => false]);
    exit;
}

require_once '../config/database.php';

$conversation_id = $_GET['conversation_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Проверяем печатает ли собеседник (не текущий пользователь)
$stmt = $pdo->prepare("
    SELECT COUNT(*) as is_typing 
    FROM typing_status 
    WHERE conversation_id = ? 
    AND user_id != ? 
    AND updated_at > DATE_SUB(NOW(), INTERVAL 5 SECOND)
");
$stmt->execute([$conversation_id, $user_id]);
$result = $stmt->fetch();

echo json_encode([
    'success' => true,
    'is_typing' => $result['is_typing'] > 0
]);
