<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Рұқсат жоқ']);
    exit;
}

require_once '../../config/database.php';

try {
    $vacancy_id = $_POST['vacancy_id'] ?? 0;
    
    // Удаляем вакансию (отклики удалятся каскадно)
    $stmt = $pdo->prepare("DELETE FROM vacancies WHERE id = ?");
    $stmt->execute([$vacancy_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Вакансия жойылды']);
    } else {
        throw new Exception('Вакансия табылмады');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
