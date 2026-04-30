<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';

// Получаем статистику
$stats = [];

// Общее количество пользователей
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type != 'admin'");
$stats['total_users'] = $stmt->fetchColumn();

// Количество HR
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'hr'");
$stats['total_hr'] = $stmt->fetchColumn();

// Количество соискателей
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'job_seeker'");
$stats['total_jobseekers'] = $stmt->fetchColumn();

// Количество вакансий
$stmt = $pdo->query("SELECT COUNT(*) FROM vacancies");
$stats['total_vacancies'] = $stmt->fetchColumn();

// Количество откликов
$stmt = $pdo->query("SELECT COUNT(*) FROM applications");
$stats['total_applications'] = $stmt->fetchColumn();

// Новые пользователи за неделю
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND user_type != 'admin'");
$stats['new_users_week'] = $stmt->fetchColumn();

// Новые вакансии за неделю
$stmt = $pdo->query("SELECT COUNT(*) FROM vacancies WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['new_vacancies_week'] = $stmt->fetchColumn();

// Новые отклики за неделю
$stmt = $pdo->query("SELECT COUNT(*) FROM applications WHERE applied_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['new_applications_week'] = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Әкімші панелі - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 250px;
            padding: 20px 0;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 25px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .admin-header {
            background: white;
            border-radius: 15px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="text-center mb-4">
            <h4><i class="fas fa-shield-alt me-2"></i>Әкімші панелі</h4>
            <small class="text-muted">HR Connect</small>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-chart-line me-2"></i>Басты бет
            </a>
            <a class="nav-link" href="users.php">
                <i class="fas fa-users me-2"></i>Қолданушылар
            </a>
            <a class="nav-link" href="vacancies.php">
                <i class="fas fa-briefcase me-2"></i>Вакансиялар
            </a>
            <a class="nav-link" href="applications.php">
                <i class="fas fa-file-alt me-2"></i>Өтінімдер
            </a>
            <hr class="text-white-50">
            <a class="nav-link" href="../dashboard.php">
                <i class="fas fa-home me-2"></i>Сайтқа өту
            </a>
            <a class="nav-link" href="../logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Шығу
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-chart-line me-2"></i>Басты бет</h2>
                    <p class="text-muted mb-0">Жүйенің жалпы статистикасы</p>
                </div>
                <div>
                    <span class="badge bg-success">Онлайн</span>
                    <span class="ms-2"><?= $_SESSION['full_name'] ?></span>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Барлық қолданушылар</p>
                            <h3 class="mb-0"><?= $stats['total_users'] ?></h3>
                            <small class="text-success">+<?= $stats['new_users_week'] ?> апта ішінде</small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">HR менеджерлер</p>
                            <h3 class="mb-0"><?= $stats['total_hr'] ?></h3>
                            <small class="text-muted">Белсенді</small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Жұмыс іздеушілер</p>
                            <h3 class="mb-0"><?= $stats['total_jobseekers'] ?></h3>
                            <small class="text-muted">Белсенді</small>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Вакансиялар</p>
                            <h3 class="mb-0"><?= $stats['total_vacancies'] ?></h3>
                            <small class="text-success">+<?= $stats['new_vacancies_week'] ?> апта ішінде</small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Барлық өтінімдер</p>
                            <h3 class="mb-0"><?= $stats['total_applications'] ?></h3>
                            <small class="text-success">+<?= $stats['new_applications_week'] ?> апта ішінде</small>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="stat-card">
                    <h5 class="mb-3"><i class="fas fa-bolt me-2"></i>Жылдам әрекеттер</h5>
                    <div class="d-grid gap-2">
                        <a href="users.php" class="btn btn-outline-primary">
                            <i class="fas fa-users me-2"></i>Қолданушыларды басқару
                        </a>
                        <a href="vacancies.php" class="btn btn-outline-success">
                            <i class="fas fa-briefcase me-2"></i>Вакансияларды басқару
                        </a>
                        <a href="applications.php" class="btn btn-outline-info">
                            <i class="fas fa-file-alt me-2"></i>Өтінімдерді қарау
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
