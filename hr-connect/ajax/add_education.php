<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Авторизация қажет']);
    exit;
}

require_once '../config/database.php';

try {
    $level = $_POST['level'] ?? '';
    $institution = $_POST['institution'] ?? '';
    $faculty = $_POST['faculty'] ?? '';
    $field_of_study = $_POST['field_of_study'] ?? '';
    $start_year = $_POST['start_year'] ?? '';
    $end_year = $_POST['end_year'] ?? '';
    
    $start_date = $start_year . '-01-01';
    $end_date = $end_year . '-12-31';
    
    $stmt = $pdo->prepare("
        INSERT INTO user_education (user_id, level, institution, faculty, field_of_study, start_date, end_date)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $level,
        $institution,
        $faculty,
        $field_of_study,
        $start_date,
        $end_date
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Білім қосылды']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
