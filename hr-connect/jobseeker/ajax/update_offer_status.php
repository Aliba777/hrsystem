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
    
    // Если оффер принят, создаем беседу (если еще не создана)
    if ($status === 'accepted') {
        // Получаем данные оффера
        $offer_data = $pdo->prepare("SELECT * FROM offers WHERE id = ?");
        $offer_data->execute([$offer_id]);
        $offer = $offer_data->fetch();
        
        // Проверяем, существует ли уже беседа
        $check_conv = $pdo->prepare("SELECT id FROM conversations WHERE offer_id = ?");
        $check_conv->execute([$offer_id]);
        
        if (!$check_conv->fetch()) {
            // Создаем новую беседу
            $create_conv = $pdo->prepare("INSERT INTO conversations (offer_id, hr_id, jobseeker_id) VALUES (?, ?, ?)");
            $create_conv->execute([$offer_id, $offer['hr_id'], $offer['job_seeker_id']]);
        }
    }
    
    $message = $status === 'accepted' ? 'Оффер қабылданды! Енді HR-мен чатта байланыса аласыз.' : 'Оффер қабылданбады!';
    
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
