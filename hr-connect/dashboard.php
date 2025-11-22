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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card h3 {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }
        .stat-card p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-handshake me-2"></i>HR Connect
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link"><i class="fas fa-user me-2"></i><?= htmlspecialchars($_SESSION['full_name']) ?></span>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Выйти</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-header">
        <div class="container">
            <h1><i class="fas fa-tachometer-alt me-3"></i>Панель управления</h1>
            <p class="mb-0">Добро пожаловать, <?= htmlspecialchars($_SESSION['full_name']) ?>!</p>
        </div>
    </div>

    <div class="container mb-5">
        <?php if ($_SESSION['user_type'] == 'hr'): ?>
            <!-- HR Dashboard -->
            <div class="row mb-4 g-4">
                <div class="col-md-6">
                    <div class="stat-card">
                        <i class="fas fa-briefcase fa-3x text-primary mb-3"></i>
                        <h3><?= $vacancies_count ?></h3>
                        <p>Мои вакансии</p>
                        <a href="hr/my_vacancies.php" class="btn btn-primary">
                            <i class="fas fa-cog me-2"></i>Управлять
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <i class="fas fa-file-alt fa-3x text-success mb-3"></i>
                        <h3><?= $applications_count ?></h3>
                        <p>Всего откликов</p>
                        <a href="hr/my_vacancies.php" class="btn btn-primary">
                            <i class="fas fa-eye me-2"></i>Просмотреть
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-3">
                <a href="hr/post_vacancy.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle me-2"></i>Добавить новую вакансию
                </a>
                <a href="hr/my_vacancies.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-list me-2"></i>Мои вакансии
                </a>
            </div>
            
        <?php else: ?>
            <!-- Job Seeker Dashboard -->
            <div class="row mb-4 g-4">
                <div class="col-md-6">
                    <div class="stat-card">
                        <i class="fas fa-paper-plane fa-3x text-primary mb-3"></i>
                        <h3><?= $my_applications_count ?></h3>
                        <p>Мои заявки</p>
                        <a href="jobseeker/my_applications.php" class="btn btn-primary">
                            <i class="fas fa-eye me-2"></i>Просмотреть
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <i class="fas fa-search fa-3x text-success mb-3"></i>
                        <h3><?= $total_vacancies ?></h3>
                        <p>Доступных вакансий</p>
                        <a href="jobseeker/browse_vacancies.php" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Найти работу
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-3">
                <a href="jobseeker/browse_vacancies.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-search me-2"></i>Найти вакансии
                </a>
                <a href="jobseeker/my_applications.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-file-alt me-2"></i>Мои заявки
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>