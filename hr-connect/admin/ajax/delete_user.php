<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Рұқсат жоқ']);
    exit;
}

require_once '../../config/database.php';

try {
    $user_id = $_POST['user_id'] ?? 0;
    
    // Проверяем что это не админ
    $check = $pdo->prepare("SELECT user_type FROM users WHERE id = ?");
    $check->execute([$user_id]);
    $user = $check->fetch();
    
    if (!$user) {
        throw new Exception('Қолданушы табылмады');
    }
    
    if ($user['user_type'] == 'admin') {
        throw new Exception('Әкімшіді жою мүмкін емес');
    }
    
    // Удаляем пользователя (связанные данные удалятся каскадно)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    echo json_encode(['success' => true, 'message' => 'Қолданушы жойылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
