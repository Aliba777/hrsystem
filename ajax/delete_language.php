<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Авторизация қажет']);
    exit;
}

require_once '../config/database.php';

try {
    $language_id = $_POST['language_id'] ?? 0;
    
    $stmt = $pdo->prepare("DELETE FROM user_languages WHERE id = ? AND user_id = ?");
    $stmt->execute([$language_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Тіл табылмады');
    }
    
    echo json_encode(['success' => true, 'message' => 'Тіл жойылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
