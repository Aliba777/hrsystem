<?php
session_start();
if ($_SESSION['user_type'] != 'hr') {
    header("Location: ../dashboard.php");
    exit;
}

require_once '../config/database.php';

if ($_POST) {
    $application_id = $_POST['application_id'];
    $status = $_POST['status'];
    
    // Проверяем, что заявка принадлежит вакансии этого HR
    $stmt = $pdo->prepare("SELECT a.* FROM applications a 
                          JOIN vacancies v ON a.vacancy_id = v.id 
                          WHERE a.id = ? AND v.hr_id = ?");
    $stmt->execute([$application_id, $_SESSION['user_id']]);
    $application = $stmt->fetch();
    
    if ($application) {
        $update_stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $update_stmt->execute([$status, $application_id]);
    }
    
    header("Location: applications.php?vacancy_id=" . $application['vacancy_id']);
    exit;
}
?>