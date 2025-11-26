<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';

// Получаем все вакансии
$stmt = $pdo->query("
    SELECT v.*, u.full_name as hr_name, u.email as hr_email,
           (SELECT COUNT(*) FROM applications WHERE vacancy_id = v.id) as applications_count
    FROM vacancies v
    JOIN users u ON v.hr_id = u.id
    ORDER BY v.created_at DESC
");
$vacancies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вакансияларды басқару - Әкімші</title>
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
        .vacancy-card {
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
            <a class="nav-link active" href="vacancies.php">
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
            <h2><i class="fas fa-briefcase me-2"></i>Вакансияларды басқару</h2>
            <p class="text-muted mb-0">Барлық вакансиялар: <?= count($vacancies) ?></p>
        </div>

        <?php foreach ($vacancies as $vacancy): ?>
            <div class="vacancy-card">
                <div class="row">
                    <div class="col-md-9">
                        <h5><?= htmlspecialchars($vacancy['title']) ?></h5>
                        <p class="text-muted mb-2">
                            <i class="fas fa-user-tie me-1"></i>HR: <?= htmlspecialchars($vacancy['hr_name']) ?> 
                            (<?= htmlspecialchars($vacancy['hr_email']) ?>)
                        </p>
                        <p class="mb-2"><?= nl2br(htmlspecialchars(substr($vacancy['description'], 0, 200))) ?>...</p>
                        <div class="d-flex gap-3">
                            <span><i class="fas fa-money-bill-wave me-1"></i><?= number_format($vacancy['salary']) ?> ₸</span>
                            <span><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($vacancy['address']) ?></span>
                            <span><i class="fas fa-file-alt me-1"></i>Өтінімдер: <?= $vacancy['applications_count'] ?></span>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>Жасалған: <?= date('d.m.Y H:i', strtotime($vacancy['created_at'])) ?>
                        </small>
                    </div>
                    <div class="col-md-3 text-end">
                        <button onclick="deleteVacancy(<?= $vacancy['id'] ?>, '<?= htmlspecialchars($vacancy['title']) ?>')" 
                                class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Жою
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteVacancy(vacancyId, title) {
            if (confirm(`"${title}" вакансиясын жою керек пе?\\n\\nНазар аударыңыз: Бұл вакансияға барлық өтінімдер жойылады!`)) {
                fetch('ajax/delete_vacancy.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `vacancy_id=${vacancyId}`
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
