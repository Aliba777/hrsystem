<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

require_once '../config/database.php';

$conversation_id = $_POST['conversation_id'] ?? 0;
$is_typing = $_POST['is_typing'] ?? 0;
$user_id = $_SESSION['user_id'];

// Проверяем доступ к беседе
$stmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (hr_id = ? OR jobseeker_id = ?)");
$stmt->execute([$conversation_id, $user_id, $user_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false]);
    exit;
}

if ($is_typing) {
    // Добавляем/обновляем статус печати
    $stmt = $pdo->prepare("
        INSERT INTO typing_status (conversation_id, user_id, updated_at) 
        VALUES (?, ?, NOW()) 
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    $stmt->execute([$conversation_id, $user_id]);
} else {
    // Удаляем статус печати
    $stmt = $pdo->prepare("DELETE FROM typing_status WHERE conversation_id = ? AND user_id = ?");
    $stmt->execute([$conversation_id, $user_id]);
}

echo json_encode(['success' => true]);
