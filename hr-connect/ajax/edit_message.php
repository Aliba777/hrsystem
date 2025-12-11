<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Рұқсат жоқ']);
    exit;
}

require_once '../config/database.php';

$message_id = $_POST['message_id'] ?? 0;
$new_message = trim($_POST['message'] ?? '');
$user_id = $_SESSION['user_id'];

if (empty($new_message)) {
    echo json_encode(['success' => false, 'message' => 'Хабарлама бос болмауы керек']);
    exit;
}

// Проверяем что сообщение принадлежит пользователю
$stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ? AND sender_id = ? AND is_deleted = 0");
$stmt->execute([$message_id, $user_id]);
$message = $stmt->fetch();

if (!$message) {
    echo json_encode(['success' => false, 'message' => 'Хабарлама табылмады']);
    exit;
}

// Обновляем сообщение
$stmt = $pdo->prepare("UPDATE messages SET message = ?, edited_at = NOW() WHERE id = ?");
if ($stmt->execute([$new_message, $message_id])) {
    echo json_encode([
        'success' => true, 
        'message' => 'Хабарлама өзгертілді',
        'new_text' => $new_message
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Қате орын алды']);
}
