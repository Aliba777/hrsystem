<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Авторизация қажет']);
    exit;
}

require_once '../config/database.php';

try {
    $language = $_POST['language'] ?? '';
    $proficiency = $_POST['proficiency'] ?? '';
    
    // Отладка - логируем полученные данные
    error_log("Add Language - Language: $language, Proficiency: $proficiency");
    
    // Проверяем, что данные не пустые
    if (empty($language)) {
        throw new Exception('Тілді таңдаңыз');
    }
    
    if (empty($proficiency)) {
        throw new Exception('Тіл деңгейін таңдаңыз');
    }
    
    // Проверяем, нет ли уже такого языка
    $check = $pdo->prepare("SELECT id FROM user_languages WHERE user_id = ? AND language = ?");
    $check->execute([$_SESSION['user_id'], $language]);
    
    if ($check->fetch()) {
        throw new Exception('Бұл тіл қосылған');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO user_languages (user_id, language, proficiency)
        VALUES (?, ?, ?)
    ");
    
    $stmt->execute([$_SESSION['user_id'], $language, $proficiency]);
    
    echo json_encode(['success' => true, 'message' => 'Тіл қосылды']);
    
} catch (Exception $e) {
    error_log("Add Language Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
