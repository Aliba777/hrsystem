<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Рұқсат жоқ']);
    exit;
}

require_once '../config/database.php';

$message_id = $_POST['message_id'] ?? 0;
$reaction = $_POST['reaction'] ?? '';
$user_id = $_SESSION['user_id'];

// Валидация реакции
$allowed_reactions = ['👍', '❤️', '😂', '😮', '👏', '🔥'];
if (!in_array($reaction, $allowed_reactions)) {
    echo json_encode(['success' => false, 'message' => 'Жарамсыз реакция']);
    exit;
}

// Проверяем существование сообщения
$stmt = $pdo->prepare("SELECT id FROM messages WHERE id = ? AND is_deleted = 0");
$stmt->execute([$message_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Хабарлама табылмады']);
    exit;
}

// Проверяем есть ли уже реакция от этого пользователя
$stmt = $pdo->prepare("SELECT reaction FROM message_reactions WHERE message_id = ? AND user_id = ?");
$stmt->execute([$message_id, $user_id]);
$existing = $stmt->fetch();

if ($existing) {
    if ($existing['reaction'] == $reaction) {
        // Удаляем реакцию если та же самая
        $stmt = $pdo->prepare("DELETE FROM message_reactions WHERE message_id = ? AND user_id = ?");
        $stmt->execute([$message_id, $user_id]);
        $action = 'removed';
    } else {
        // Обновляем реакцию
        $stmt = $pdo->prepare("UPDATE message_reactions SET reaction = ? WHERE message_id = ? AND user_id = ?");
        $stmt->execute([$reaction, $message_id, $user_id]);
        $action = 'updated';
    }
} else {
    // Добавляем новую реакцию
    $stmt = $pdo->prepare("INSERT INTO message_reactions (message_id, user_id, reaction) VALUES (?, ?, ?)");
    $stmt->execute([$message_id, $user_id, $reaction]);
    $action = 'added';
}

// Получаем все реакции для этого сообщения
$stmt = $pdo->prepare("
    SELECT reaction, COUNT(*) as count 
    FROM message_reactions 
    WHERE message_id = ? 
    GROUP BY reaction
");
$stmt->execute([$message_id]);
$reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'action' => $action,
    'reactions' => $reactions
]);
