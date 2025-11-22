<?php
session_start();
if ($_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../dashboard.php");
    exit;
}

require_once '../config/database.php';

if ($_POST) {
    $vacancy_id = $_POST['vacancy_id'];
    $cover_letter = $_POST['cover_letter'];
    $job_seeker_id = $_SESSION['user_id'];
    
    // Проверяем, не откликался ли уже
    $check_stmt = $pdo->prepare("SELECT * FROM applications WHERE vacancy_id = ? AND job_seeker_id = ?");
    $check_stmt->execute([$vacancy_id, $job_seeker_id]);
    
    if ($check_stmt->fetch()) {
        $_SESSION['error'] = "Вы уже откликались на эту вакансию";
    } else {
        $stmt = $pdo->prepare("INSERT INTO applications (vacancy_id, job_seeker_id, cover_letter) VALUES (?, ?, ?)");
        $stmt->execute([$vacancy_id, $job_seeker_id, $cover_letter]);
        $_SESSION['success'] = "Отклик успешно отправлен!";
    }
    
    header("Location: my_applications.php");
    exit;
}
?>