<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Авторизация қажет']);
    exit;
}

require_once '../config/database.php';

try {
    $image_type = $_GET['image_type'] ?? 'resume';
    
    $stmt = $pdo->prepare("
        SELECT * FROM user_images 
        WHERE user_id = ? AND image_type = ? 
        ORDER BY display_order ASC
    ");
    
    $stmt->execute([$_SESSION['user_id'], $image_type]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'images' => $images
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
