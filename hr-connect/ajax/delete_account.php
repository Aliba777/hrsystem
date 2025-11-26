<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Авторизация қажет']);
    exit;
}

require_once '../config/database.php';

try {
    // Удаляем пользователя (каскадное удаление удалит все связанные данные)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    // Очищаем сессию
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'Профиль жойылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
