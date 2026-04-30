<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Авторизация қажет']);
    exit;
}

require_once '../config/database.php';

try {
    $contact_type = $_POST['contact_type'] ?? '';
    $contact_value = $_POST['contact_value'] ?? '';
    
    $allowed_types = ['telegram', 'whatsapp', 'instagram', 'email', 'phone'];
    
    if (!in_array($contact_type, $allowed_types)) {
        throw new Exception('Жарамсыз байланыс түрі');
    }
    
    $stmt = $pdo->prepare("UPDATE users SET $contact_type = ? WHERE id = ?");
    $stmt->execute([$contact_value, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Байланыс жаңартылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
