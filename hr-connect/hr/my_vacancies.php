<?php
session_start();
if ($_SESSION['user_type'] != 'hr') {
    header("Location: ../dashboard.php");
    exit;
}

require_once '../config/database.php';

$stmt = $pdo->prepare("SELECT * FROM vacancies WHERE hr_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$vacancies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои вакансии - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">HR Connect</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="post_vacancy.php">Добавить вакансию</a>
                <a class="nav-link" href="../logout.php">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Мои вакансии</h2>
        
        <?php if (empty($vacancies)): ?>
            <div class="alert alert-info">
                У вас пока нет вакансий. <a href="post_vacancy.php">Создайте первую!</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($vacancies as $vacancy): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($vacancy['title']) ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars($vacancy['description'])) ?></p>
                            <p class="text-success fw-bold">Зарплата: <?= number_format($vacancy['salary'], 0, '', ' ') ?> руб.</p>
                            <p class="text-muted">Адрес: <?= htmlspecialchars($vacancy['address']) ?></p>
                            <p class="text-muted small">Опубликовано: <?= date('d.m.Y', strtotime($vacancy['created_at'])) ?></p>
                            
                            <div class="mt-3">
                                <a href="applications.php?vacancy_id=<?= $vacancy['id'] ?>" class="btn btn-primary btn-sm">
                                    Посмотреть отклики
                                </a>
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