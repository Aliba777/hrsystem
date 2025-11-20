<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// Получаем статистику для дашборда
if ($_SESSION['user_type'] == 'hr') {
    $vacancies_stmt = $pdo->prepare("SELECT COUNT(*) FROM vacancies WHERE hr_id = ?");
    $vacancies_stmt->execute([$_SESSION['user_id']]);
    $vacancies_count = $vacancies_stmt->fetchColumn();
    
    $applications_stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a 
                                       JOIN vacancies v ON a.vacancy_id = v.id 
                                       WHERE v.hr_id = ?");
    $applications_stmt->execute([$_SESSION['user_id']]);
    $applications_count = $applications_stmt->fetchColumn();
} else {
    $applications_stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE job_seeker_id = ?");
    $applications_stmt->execute([$_SESSION['user_id']]);
    $my_applications_count = $applications_stmt->fetchColumn();
    
    $vacancies_stmt = $pdo->query("SELECT COUNT(*) FROM vacancies");
    $total_vacancies = $vacancies_stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Дашборд - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">HR Connect</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link">Привет, <?= htmlspecialchars($_SESSION['full_name']) ?>!</span>
                <a class="nav-link" href="logout.php">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Дашборд</h2>
        
        <?php if ($_SESSION['user_type'] == 'hr'): ?>
            <!-- HR Dashboard -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3><?= $vacancies_count ?></h3>
                            <p>Мои вакансии</p>
                            <a href="hr/my_vacancies.php" class="btn btn-primary">Управлять</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3><?= $applications_count ?></h3>
                            <p>Всего откликов</p>
                            <a href="hr/my_vacancies.php" class="btn btn-primary">Просмотреть</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <a href="hr/post_vacancy.php" class="btn btn-primary btn-lg">Добавить новую вакансию</a>
                <a href="hr/my_vacancies.php" class="btn btn-outline-primary">Мои вакансии</a>
            </div>
            
        <?php else: ?>
            <!-- Job Seeker Dashboard -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3><?= $my_applications_count ?></h3>
                            <p>Мои заявки</p>
                            <a href="jobseeker/my_applications.php" class="btn btn-primary">Просмотреть</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3><?= $total_vacancies ?></h3>
                            <p>Доступных вакансий</p>
                            <a href="jobseeker/browse_vacancies.php" class="btn btn-primary">Найти работу</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <a href="jobseeker/browse_vacancies.php" class="btn btn-primary btn-lg">Найти вакансии</a>
                <a href="jobseeker/my_applications.php" class="btn btn-outline-primary">Мои заявки</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/script.js"></script>
</body>
</html>