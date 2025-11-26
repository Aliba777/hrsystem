<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Авторизация қажет']);
    exit;
}

require_once '../config/database.php';

try {
    $image_id = $_POST['image_id'] ?? 0;
    
    // Получаем информацию об изображении
    $stmt = $pdo->prepare("SELECT * FROM user_images WHERE id = ? AND user_id = ?");
    $stmt->execute([$image_id, $_SESSION['user_id']]);
    $image = $stmt->fetch();
    
    if (!$image) {
        throw new Exception('Сурет табылмады');
    }
    
    // Удаляем файл
    $file_path = "../" . $image['image_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Удаляем из базы данных
    $delete_stmt = $pdo->prepare("DELETE FROM user_images WHERE id = ?");
    $delete_stmt->execute([$image_id]);
    
    echo json_encode(['success' => true, 'message' => 'Сурет жойылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
