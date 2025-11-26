<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Авторизация қажет']);
    exit;
}

require_once '../config/database.php';

try {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $city = $_POST['city'] ?? '';
    $birth_date = $_POST['birth_date'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $citizenship = $_POST['citizenship'] ?? '';
    
    $stmt = $pdo->prepare("
        UPDATE users SET 
            first_name = ?, 
            last_name = ?, 
            city = ?, 
            birth_date = ?, 
            gender = ?, 
            citizenship = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $first_name,
        $last_name,
        $city,
        $birth_date ?: null,
        $gender ?: null,
        $citizenship,
        $_SESSION['user_id']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Деректер жаңартылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
