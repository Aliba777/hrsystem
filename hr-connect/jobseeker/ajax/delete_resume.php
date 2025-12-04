<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'job_seeker') {
    echo json_encode(['success' => false, 'message' => 'Рұқсат жоқ']);
    exit;
}

require_once '../../config/database.php';

try {
    $resume_id = $_POST['resume_id'] ?? 0;
    
    // Проверяем что резюме принадлежит текущему пользователю
    $check = $pdo->prepare("SELECT id FROM resumes WHERE id = ? AND job_seeker_id = ?");
    $check->execute([$resume_id, $_SESSION['user_id']]);
    
    if (!$check->fetch()) {
        throw new Exception('Резюме табылмады');
    }
    
    // Удаляем резюме (офферы удалятся каскадно)
    $stmt = $pdo->prepare("DELETE FROM resumes WHERE id = ? AND job_seeker_id = ?");
    $stmt->execute([$resume_id, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Резюме жойылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
