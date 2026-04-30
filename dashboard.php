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
    
    // Количество отправленных офферов
    $offers_stmt = $pdo->prepare("SELECT COUNT(*) FROM offers WHERE hr_id = ?");
    $offers_stmt->execute([$_SESSION['user_id']]);
    $offers_count = $offers_stmt->fetchColumn();
    
    // Количество активных резюме
    $resumes_stmt = $pdo->query("SELECT COUNT(*) FROM resumes WHERE status = 'active'");
    $total_resumes = $resumes_stmt->fetchColumn();
} else {
    $applications_stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE job_seeker_id = ?");
    $applications_stmt->execute([$_SESSION['user_id']]);
    $my_applications_count = $applications_stmt->fetchColumn();
    
    $vacancies_stmt = $pdo->query("SELECT COUNT(*) FROM vacancies");
    $total_vacancies = $vacancies_stmt->fetchColumn();
    
    // Количество резюме пользователя
    $resumes_stmt = $pdo->prepare("SELECT COUNT(*) FROM resumes WHERE job_seeker_id = ?");
    $resumes_stmt->execute([$_SESSION['user_id']]);
    $my_resumes_count = $resumes_stmt->fetchColumn();
    
    // Количество полученных офферов
    $offers_stmt = $pdo->prepare("SELECT COUNT(*) FROM offers WHERE job_seeker_id = ?");
    $offers_stmt->execute([$_SESSION['user_id']]);
    $my_offers_count = $offers_stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Басты бет - HR Connect</title>
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
    <?php include 'includes/navbar.php'; ?>

    <div class="dashboard-header">
        <div class="container">
            <h1><i class="fas fa-tachometer-alt me-3"></i>Басқару панелі</h1>
            <p class="mb-0">Қош келдіңіз, <?= htmlspecialchars($_SESSION['full_name']) ?>!</p>
        </div>
    </div>

    <div class="container mb-5">
        <?php if ($_SESSION['user_type'] == 'hr'): ?>
            <!-- HR Dashboard -->
            <div class="row mb-4 g-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-briefcase fa-3x text-primary mb-3"></i>
                        <h3><?= $vacancies_count ?></h3>
                        <p>Менің вакансияларым</p>
                        <a href="hr/my_vacancies.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-cog me-2"></i>Басқару
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                        <h3><?= $applications_count ?></h3>
                        <p>Өтінімдер</p>
                        <a href="hr/my_vacancies.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye me-2"></i>Қарау
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h3><?= $total_resumes ?></h3>
                        <p>Резюмелер</p>
                        <a href="resumes.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-search me-2"></i>Қарау
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-paper-plane fa-3x text-primary mb-3"></i>
                        <h3><?= $offers_count ?></h3>
                        <p>Менің офферлерім</p>
                        <a href="hr/my_offers.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-envelope me-2"></i>Қарау
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="hr/post_vacancy.php" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-plus-circle me-2"></i>Жаңа вакансия қосу
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="resumes.php" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-users me-2"></i>Резюмелерді қарау
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="hr/my_vacancies.php" class="btn btn-outline-primary btn-lg w-100">
                        <i class="fas fa-list me-2"></i>Менің вакансияларым
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="hr/my_offers.php" class="btn btn-outline-primary btn-lg w-100">
                        <i class="fas fa-envelope me-2"></i>Менің офферлерім
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Job Seeker Dashboard -->
            <div class="row mb-4 g-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-paper-plane fa-3x text-primary mb-3"></i>
                        <h3><?= $my_applications_count ?></h3>
                        <p>Менің өтінімдерім</p>
                        <a href="jobseeker/my_applications.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye me-2"></i>Қарау
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-search fa-3x text-primary mb-3"></i>
                        <h3><?= $total_vacancies ?></h3>
                        <p>Вакансиялар</p>
                        <a href="jobseeker/browse_vacancies.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-search me-2"></i>Іздеу
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                        <h3><?= $my_resumes_count ?></h3>
                        <p>Менің резюмелерім</p>
                        <a href="jobseeker/my_resumes.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-folder me-2"></i>Қарау
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                        <h3><?= $my_offers_count ?></h3>
                        <p>Офферлер</p>
                        <a href="jobseeker/my_resumes.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-envelope me-2"></i>Қарау
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="jobseeker/post_resume.php" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-plus-circle me-2"></i>Резюме жариялау
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="jobseeker/browse_vacancies.php" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-search me-2"></i>Вакансияларды іздеу
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="jobseeker/my_resumes.php" class="btn btn-outline-primary btn-lg w-100">
                        <i class="fas fa-folder me-2"></i>Менің резюмелерім
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="jobseeker/my_applications.php" class="btn btn-outline-primary btn-lg w-100">
                        <i class="fas fa-file-alt me-2"></i>Менің өтінімдерім
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>