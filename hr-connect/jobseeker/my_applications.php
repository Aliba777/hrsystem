<?php
session_start();
if ($_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../dashboard.php");
    exit;
}

require_once '../config/database.php';

$stmt = $pdo->prepare("SELECT a.*, v.title as vacancy_title, v.salary, v.address, u.full_name as hr_name 
                      FROM applications a 
                      JOIN vacancies v ON a.vacancy_id = v.id 
                      JOIN users u ON v.hr_id = u.id 
                      WHERE a.job_seeker_id = ? 
                      ORDER BY a.applied_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">HR Connect</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="browse_vacancies.php">Поиск вакансий</a>
                <a class="nav-link" href="../logout.php">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Мои заявки</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (empty($applications)): ?>
            <div class="alert alert-info">
                У вас пока нет отправленных заявок. <a href="browse_vacancies.php">Найдите вакансии и откликнитесь!</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($applications as $application): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($application['vacancy_title']) ?></h5>
                            <p class="text-success fw-bold">Зарплата: <?= number_format($application['salary'], 0, '', ' ') ?> руб.</p>
                            <p class="text-muted">Адрес: <?= htmlspecialchars($application['address']) ?></p>
                            <p class="text-muted">HR: <?= htmlspecialchars($application['hr_name']) ?></p>
                            
                            <div class="mb-3">
                                <strong>Сопроводительное письмо:</strong>
                                <p><?= nl2br(htmlspecialchars($application['cover_letter'])) ?></p>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-<?= $application['status'] == 'pending' ? 'warning' : ($application['status'] == 'accepted' ? 'success' : 'danger') ?>">
                                    <?= $application['status'] == 'pending' ? 'На рассмотрении' : ($application['status'] == 'accepted' ? 'Принято' : 'Отклонено') ?>
                                </span>
                                <small class="text-muted">
                                    <?= date('d.m.Y H:i', strtotime($application['applied_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>