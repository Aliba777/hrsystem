<?php
session_start();
header('Content-Type: application/json');

if ($_SESSION['user_type'] != 'hr') {
    echo json_encode(['success' => false, 'message' => 'Рұқсат жоқ']);
    exit;
}

require_once '../../config/database.php';

try {
    $vacancy_id = $_POST['vacancy_id'] ?? 0;
    
    // Проверяем, что вакансия принадлежит этому HR
    $check = $pdo->prepare("SELECT id FROM vacancies WHERE id = ? AND hr_id = ?");
    $check->execute([$vacancy_id, $_SESSION['user_id']]);
    
    if (!$check->fetch()) {
        throw new Exception('Вакансия табылмады');
    }
    
    // Удаляем вакансию (отклики удалятся автоматически благодаря ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM vacancies WHERE id = ? AND hr_id = ?");
    $stmt->execute([$vacancy_id, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Вакансия жойылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
