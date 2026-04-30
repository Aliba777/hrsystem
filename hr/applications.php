<?php
session_start();
if ($_SESSION['user_type'] != 'hr') {
    header("Location: ../dashboard.php");
    exit;
}

require_once '../config/database.php';

$vacancy_id = $_GET['vacancy_id'];

// Получаем информацию о вакансии
$vacancy_stmt = $pdo->prepare("SELECT title FROM vacancies WHERE id = ? AND hr_id = ?");
$vacancy_stmt->execute([$vacancy_id, $_SESSION['user_id']]);
$vacancy = $vacancy_stmt->fetch();

if (!$vacancy) {
    header("Location: my_vacancies.php");
    exit;
}

// Получаем отклики
$stmt = $pdo->prepare("SELECT a.*, u.full_name, u.email, u.phone, v.title as vacancy_title 
                      FROM applications a 
                      JOIN users u ON a.job_seeker_id = u.id 
                      JOIN vacancies v ON a.vacancy_id = v.id 
                      WHERE a.vacancy_id = ? AND v.hr_id = ?
                      ORDER BY a.applied_at DESC");
$stmt->execute([$vacancy_id, $_SESSION['user_id']]);
$applications = $stmt->fetchAll();

// Статистика
$total_applications = count($applications);
$pending_count = 0;
$accepted_count = 0;
$rejected_count = 0;

foreach ($applications as $app) {
    if ($app['status'] == 'pending') $pending_count++;
    if ($app['status'] == 'accepted') $accepted_count++;
    if ($app['status'] == 'rejected') $rejected_count++;
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Өтінімдер - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #212529;
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 30px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .applications-container {
            padding: 40px 0;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.total { border-left: 4px solid var(--primary); }
        .stat-card.pending { border-left: 4px solid var(--warning); }
        .stat-card.accepted { border-left: 4px solid var(--success); }
        .stat-card.rejected { border-left: 4px solid var(--danger); }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
        }

        .application-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-left: 5px solid var(--warning);
            transition: all 0.3s ease;
        }

        .application-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .application-card.accepted {
            border-left-color: var(--success);
        }

        .application-card.rejected {
            border-left-color: var(--danger);
        }

        .candidate-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 20px;
        }

        .candidate-info {
            flex: 1;
        }

        .candidate-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .candidate-contacts {
            color: #666;
        }

        .contact-item {
            margin-bottom: 5px;
        }

        .contact-item i {
            width: 20px;
            color: var(--primary);
        }

        .application-status {
            text-align: right;
        }

        .status-badge {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .cover-letter {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }

        .cover-letter h6 {
            color: var(--primary);
            margin-bottom: 10px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-success, .btn-danger {
            border-radius: 12px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #e83e8c);
        }

        .btn-success:hover, .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ddd;
        }

        .back-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: var(--secondary);
        }
    </style>
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-handshake me-2"></i>HR Connect
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="my_vacancies.php"><i class="fas fa-list me-1"></i>Менің вакансияларым</a>
                <a class="nav-link" href="post_vacancy.php"><i class="fas fa-plus me-1"></i>Вакансия қосу</a>
                <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-1"></i>Шығу</a>
            </div>
        </div>
    </nav>

    <div class="applications-container">
        <div class="container">
            <!-- Заголовок страницы -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <a href="my_vacancies.php" class="back-link">
                            <i class="fas fa-arrow-left me-2"></i>Вакансиялар тізіміне оралу
                        </a>
                        <h1 class="mt-2">Өтінімдер</h1>
                        <p class="lead mb-0">"<?= htmlspecialchars($vacancy['title']) ?>" вакансиясы бойынша</p>
                    </div>
                    <div class="text-end">
                        <div class="text-muted">Барлығы</div>
                        <div class="h2 mb-0"><?= $total_applications ?></div>
                        <div class="text-muted">өтінім</div>
                    </div>
                </div>

                <!-- Статистика -->
                <div class="stats-cards">
                    <div class="stat-card total">
                        <div class="stat-number"><?= $total_applications ?></div>
                        <div class="stat-label">Барлығы</div>
                    </div>
                    <div class="stat-card pending">
                        <div class="stat-number"><?= $pending_count ?></div>
                        <div class="stat-label">Қарастыруда</div>
                    </div>
                    <div class="stat-card accepted">
                        <div class="stat-number"><?= $accepted_count ?></div>
                        <div class="stat-label">Қабылданған</div>
                    </div>
                    <div class="stat-card rejected">
                        <div class="stat-number"><?= $rejected_count ?></div>
                        <div class="stat-label">Қабылданбаған</div>
                    </div>
                </div>
            </div>

            <!-- Список откликов -->
            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Әлі өтінімдер жоқ</h3>
                    <p>Бұл вакансия бойынша әлі ешкім өтінім берген жоқ.</p>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                <div class="application-card <?= $app['status'] ?>">
                    <div class="candidate-header">
                        <div class="candidate-info">
                            <div class="candidate-name">
                                <?= htmlspecialchars($app['full_name']) ?>
                            </div>
                            <div class="candidate-contacts">
                                <div class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <?= htmlspecialchars($app['email']) ?>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <?= htmlspecialchars($app['phone']) ?>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-clock"></i>
                                    Өтінім берді: <?= date('d.m.Y H:i', strtotime($app['applied_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="application-status">
                            <span class="status-badge bg-<?= $app['status'] == 'pending' ? 'warning' : ($app['status'] == 'accepted' ? 'success' : 'danger') ?>">
                                <?= $app['status'] == 'pending' ? 'Қарастыруда' : ($app['status'] == 'accepted' ? 'Қабылданған' : 'Қабылданбаған') ?>
                            </span>
                        </div>
                    </div>

                    <div class="cover-letter">
                        <h6><i class="fas fa-file-alt me-2"></i>Өтінім хаты</h6>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($app['cover_letter'])) ?></p>
                    </div>

                    <?php if ($app['status'] == 'pending'): ?>
                    <div class="action-buttons">
                        <form action="process_application.php" method="POST" class="d-inline">
                            <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Қабылдау
                            </button>
                        </form>
                        
                        <form action="process_application.php" method="POST" class="d-inline">
                            <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times me-2"></i>Қабылдамау
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Подтверждение действий
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const status = this.querySelector('input[name="status"]').value;
                const candidateName = this.closest('.application-card').querySelector('.candidate-name').textContent;
                
                let message = '';
                if (status === 'accepted') {
                    message = `"${candidateName}" өтінімін қабылдағыңыз келе ме?`;
                } else {
                    message = `"${candidateName}" өтінімін қабылдамағыңыз келе ме?`;
                }
                
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });

        // Анимация появления карточек
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.application-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>