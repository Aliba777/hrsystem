<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';

// Получаем все отклики
$stmt = $pdo->query("
    SELECT a.*, 
           v.title as vacancy_title,
           js.full_name as jobseeker_name, js.email as jobseeker_email,
           hr.full_name as hr_name
    FROM applications a
    JOIN vacancies v ON a.vacancy_id = v.id
    JOIN users js ON a.job_seeker_id = js.id
    JOIN users hr ON v.hr_id = hr.id
    ORDER BY a.applied_at DESC
");
$applications = $stmt->fetchAll();

$statusColors = [
    'pending' => 'warning',
    'accepted' => 'success',
    'rejected' => 'danger'
];

$statusTexts = [
    'pending' => 'Күту',
    'accepted' => 'Қабылданды',
    'rejected' => 'Қабылданбады'
];
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Өтінімдер - Әкімші</title>
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
        .application-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
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
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-chart-line me-2"></i>Басты бет
            </a>
            <a class="nav-link" href="users.php">
                <i class="fas fa-users me-2"></i>Қолданушылар
            </a>
            <a class="nav-link" href="vacancies.php">
                <i class="fas fa-briefcase me-2"></i>Вакансиялар
            </a>
            <a class="nav-link active" href="applications.php">
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
            <h2><i class="fas fa-file-alt me-2"></i>Барлық өтінімдер</h2>
            <p class="text-muted mb-0">Барлығы: <?= count($applications) ?></p>
        </div>

        <?php foreach ($applications as $app): ?>
            <div class="application-card">
                <div class="row">
                    <div class="col-md-10">
                        <h5>
                            <i class="fas fa-briefcase me-2"></i><?= htmlspecialchars($app['vacancy_title']) ?>
                            <span class="badge bg-<?= $statusColors[$app['status']] ?> ms-2">
                                <?= $statusTexts[$app['status']] ?>
                            </span>
                        </h5>
                        <p class="mb-1">
                            <i class="fas fa-user me-1"></i><strong>Жұмыс іздеуші:</strong> 
                            <?= htmlspecialchars($app['jobseeker_name']) ?> 
                            (<?= htmlspecialchars($app['jobseeker_email']) ?>)
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-user-tie me-1"></i><strong>HR:</strong> 
                            <?= htmlspecialchars($app['hr_name']) ?>
                        </p>
                        <?php if ($app['cover_letter']): ?>
                            <p class="mb-1"><strong>Жолдама хат:</strong></p>
                            <p class="text-muted"><?= nl2br(htmlspecialchars(substr($app['cover_letter'], 0, 150))) ?>...</p>
                        <?php endif; ?>
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>Берілген: <?= date('d.m.Y H:i', strtotime($app['applied_at'])) ?>
                        </small>
                    </div>
                    <div class="col-md-2 text-end">
                        <button onclick="deleteApplication(<?= $app['id'] ?>)" class="btn btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteApplication(appId) {
            if (confirm('Бұл өтінімді жою керек пе?')) {
                fetch('ajax/delete_application.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `application_id=${appId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Қате: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Қате орын алды!');
                });
            }
        }
    </script>
</body>
</html>
