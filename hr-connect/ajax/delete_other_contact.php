<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Авторизация қажет']);
    exit;
}

require_once '../config/database.php';

try {
    $contact_id = $_POST['contact_id'] ?? 0;
    
    // Проверяем, что контакт принадлежит пользователю
    $check = $pdo->prepare("SELECT id FROM user_other_contacts WHERE id = ? AND user_id = ?");
    $check->execute([$contact_id, $_SESSION['user_id']]);
    
    if (!$check->fetch()) {
        throw new Exception('Байланыс табылмады');
    }
    
    $stmt = $pdo->prepare("DELETE FROM user_other_contacts WHERE id = ? AND user_id = ?");
    $stmt->execute([$contact_id, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Байланыс жойылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
