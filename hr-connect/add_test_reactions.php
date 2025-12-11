<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo "Войдите в систему для тестирования";
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем первое сообщение для тестирования
$stmt = $pdo->query("SELECT id FROM messages LIMIT 1");
$message = $stmt->fetch();

if (!$message) {
    echo "Нет сообщений для тестирования";
    exit;
}

$message_id = $message['id'];

// Добавляем тестовые реакции
$reactions = ['👍', '❤️', '😂'];

foreach ($reactions as $reaction) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO message_reactions (message_id, user_id, reaction) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE reaction = VALUES(reaction)
        ");
        $stmt->execute([$message_id, $user_id, $reaction]);
        echo "✓ Добавлена реакция $reaction к сообщению $message_id<br>";
    } catch (PDOException $e) {
        echo "✗ Ошибка: " . $e->getMessage() . "<br>";
    }
}

echo "<br><a href='messages.php'>Вернуться к сообщениям</a>";
?>