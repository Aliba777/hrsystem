<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';

// Получаем всех пользователей
$stmt = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM vacancies WHERE hr_id = u.id) as vacancies_count,
           (SELECT COUNT(*) FROM applications WHERE job_seeker_id = u.id) as applications_count
    FROM users u 
    WHERE u.user_type != 'admin'
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Қолданушыларды басқару - Әкімші</title>
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
        .user-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .user-card:hover {
            transform: translateY(-3px);
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
            <a class="nav-link active" href="users.php">
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
            <h2><i class="fas fa-users me-2"></i>Қолданушыларды басқару</h2>
            <p class="text-muted mb-0">Барлық қолданушылар: <?= count($users) ?></p>
        </div>

        <div class="row">
            <?php foreach ($users as $user): ?>
                <div class="col-md-6 mb-3">
                    <div class="user-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h5 class="mb-1">
                                    <i class="fas fa-user me-2"></i><?= htmlspecialchars($user['full_name']) ?>
                                    <?php if ($user['user_type'] == 'hr'): ?>
                                        <span class="badge bg-success">HR</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Жұмыс іздеуші</span>
                                    <?php endif; ?>
                                </h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($user['email']) ?>
                                </p>
                                <?php if ($user['phone']): ?>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-phone me-1"></i><?= htmlspecialchars($user['phone']) ?>
                                    </p>
                                <?php endif; ?>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>Тіркелген: <?= date('d.m.Y', strtotime($user['created_at'])) ?>
                                </small>
                                
                                <?php if ($user['user_type'] == 'hr'): ?>
                                    <div class="mt-2">
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-briefcase me-1"></i>Вакансиялар: <?= $user['vacancies_count'] ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-2">
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-file-alt me-1"></i>Өтінімдер: <?= $user['applications_count'] ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="btn-group-vertical">
                                <button onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['full_name']) ?>')" 
                                        class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteUser(userId, userName) {
            if (confirm(`"${userName}" қолданушысын жою керек пе?\\n\\nНазар аударыңыз: Барлық байланысты деректер жойылады!`)) {
                fetch('ajax/delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
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
