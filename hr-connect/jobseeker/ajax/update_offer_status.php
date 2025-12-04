<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'job_seeker') {
    echo json_encode(['success' => false, 'message' => 'Рұқсат жоқ']);
    exit;
}

require_once '../../config/database.php';

try {
    $offer_id = $_POST['offer_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    // Проверяем валидность статуса
    if (!in_array($status, ['accepted', 'rejected'])) {
        throw new Exception('Жарамсыз статус');
    }
    
    // Проверяем что оффер для текущего пользователя
    $check = $pdo->prepare("SELECT id FROM offers WHERE id = ? AND job_seeker_id = ?");
    $check->execute([$offer_id, $_SESSION['user_id']]);
    
    if (!$check->fetch()) {
        throw new Exception('Оффер табылмады');
    }
    
    // Обновляем статус
    $update = $pdo->prepare("UPDATE offers SET status = ? WHERE id = ?");
    $update->execute([$status, $offer_id]);
    
    $message = $status === 'accepted' ? 'Оффер қабылданды!' : 'Оффер қабылданбады!';
    
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
