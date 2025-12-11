<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

// Получаем количество непрочитанных сообщений
$stmt = $pdo->prepare("
    SELECT COUNT(*) as unread_count
    FROM messages m
    JOIN conversations c ON m.conversation_id = c.id
    WHERE m.sender_id != ? 
    AND m.is_read = 0
    AND (c.hr_id = ? OR c.jobseeker_id = ?)
");
$stmt->execute([$user_id, $user_id, $user_id]);
$result = $stmt->fetch();

echo json_encode([
    'success' => true,
    'count' => (int)$result['unread_count']
]);
