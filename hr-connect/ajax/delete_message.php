<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Рұқсат жоқ']);
    exit;
}

require_once '../config/database.php';

$message_id = $_POST['message_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Проверяем что сообщение принадлежит пользователю
$stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ? AND sender_id = ?");
$stmt->execute([$message_id, $user_id]);
$message = $stmt->fetch();

if (!$message) {
    echo json_encode(['success' => false, 'message' => 'Хабарлама табылмады']);
    exit;
}

// Помечаем как удаленное (мягкое удаление)
$stmt = $pdo->prepare("UPDATE messages SET is_deleted = 1, message = '[Хабарлама жойылды]' WHERE id = ?");
if ($stmt->execute([$message_id])) {
    echo json_encode(['success' => true, 'message' => 'Хабарлама жойылды']);
} else {
    echo json_encode(['success' => false, 'message' => 'Қате орын алды']);
}
