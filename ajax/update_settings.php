<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Авторизация қажет']);
    exit;
}

require_once '../config/database.php';

try {
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Проверка пароля
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            throw new Exception('Құпия сөздер сәйкес келмейді');
        }
        
        if (strlen($new_password) < 6) {
            throw new Exception('Құпия сөз кемінде 6 таңбадан тұруы керек');
        }
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            UPDATE users SET email = ?, phone = ?, password = ? WHERE id = ?
        ");
        $stmt->execute([$email, $phone, $hashed_password, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE users SET email = ?, phone = ? WHERE id = ?
        ");
        $stmt->execute([$email, $phone, $_SESSION['user_id']]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Баптаулар жаңартылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
