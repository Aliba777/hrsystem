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
    $contact_url = $_POST['contact_url'] ?? '';
    $contact_description = $_POST['contact_description'] ?? '';
    
    $stmt = $pdo->prepare("
        INSERT INTO user_other_contacts (user_id, contact_type, contact_url, contact_description)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $contact_type,
        $contact_url,
        $contact_description
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Байланыс әдісі қосылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
