<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'hr') {
    echo json_encode(['success' => false, 'message' => 'Рұқсат жоқ']);
    exit;
}

require_once '../../config/database.php';

$offer_id = $_POST['offer_id'] ?? 0;

// Проверяем, что оффер принадлежит текущему HR
$stmt = $pdo->prepare("SELECT id FROM offers WHERE id = ? AND hr_id = ?");
$stmt->execute([$offer_id, $_SESSION['user_id']]);
$offer = $stmt->fetch();

if (!$offer) {
    echo json_encode(['success' => false, 'message' => 'Оффер табылмады']);
    exit;
}

// Удаляем оффер
$stmt = $pdo->prepare("DELETE FROM offers WHERE id = ? AND hr_id = ?");
if ($stmt->execute([$offer_id, $_SESSION['user_id']])) {
    // Проверяем, что оффер действительно удален
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Оффер сәтті жойылды']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Оффер табылмады немесе жойылған']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Қате орын алды']);
}
