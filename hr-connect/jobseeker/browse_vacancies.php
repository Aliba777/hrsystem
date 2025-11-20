<?php
session_start();
if ($_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../dashboard.php");
    exit;
}

require_once '../config/database.php';

// Поиск и фильтрация
$search = $_GET['search'] ?? '';
$min_salary = $_GET['min_salary'] ?? '';

$sql = "SELECT v.*, u.full_name as hr_name FROM vacancies v 
        JOIN users u ON v.hr_id = u.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (v.title LIKE ? OR v.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($min_salary)) {
    $sql .= " AND v.salary >= ?";
    $params[] = $min_salary;
}

$sql .= " ORDER BY v.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vacancies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск вакансий - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">HR Connect</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="my_applications.php">Мои заявки</a>
                <a class="nav-link" href="../logout.php">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Поиск вакансий</h2>
        
        <!-- Форма поиска -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Поиск по названию или описанию" value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="min_salary" class="form-control" placeholder="Минимальная зарплата" value="<?= htmlspecialchars($min_salary) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Найти</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Список вакансий -->
        <?php if (empty($vacancies)): ?>
            <div class="alert alert-info">
                Вакансии не найдены. Попробуйте изменить параметры поиска.
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
                            <p class="text-muted">HR: <?= htmlspecialchars($vacancy['hr_name']) ?></p>
                            
                            <form action="apply.php" method="POST">
                                <input type="hidden" name="vacancy_id" value="<?= $vacancy['id'] ?>">
                                <div class="mb-3">
                                    <textarea name="cover_letter" class="form-control" placeholder="Напишите сопроводительное письмо..." rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Откликнуться на вакансию</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>