<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Авторизация қажет']);
    exit;
}

require_once '../config/database.php';

try {
    $education_id = $_POST['education_id'] ?? 0;
    
    $stmt = $pdo->prepare("DELETE FROM user_education WHERE id = ? AND user_id = ?");
    $stmt->execute([$education_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Білім табылмады');
    }
    
    echo json_encode(['success' => true, 'message' => 'Білім жойылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
