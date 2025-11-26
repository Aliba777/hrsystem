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
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Менің вакансияларым - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-handshake me-2"></i>HR Connect
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="post_vacancy.php"><i class="fas fa-plus-circle me-2"></i>Вакансия қосу</a>
                <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Шығу</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <h2 class="mb-4"><i class="fas fa-briefcase me-2"></i>Менің вакансияларым</h2>
        
        <?php if (empty($vacancies)): ?>
            <div class="alert alert-info">
                Сізде әлі вакансиялар жоқ. <a href="post_vacancy.php">Бірінші вакансияны жасаңыз!</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($vacancies as $vacancy): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($vacancy['title']) ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars($vacancy['description'])) ?></p>
                            <p class="text-success fw-bold">Жалақы: <?= number_format($vacancy['salary'], 0, '', ' ') ?> ₸</p>
                            <p class="text-muted">Мекен-жайы: <?= htmlspecialchars($vacancy['address']) ?></p>
                            <p class="text-muted small">Жарияланды: <?= date('d.m.Y', strtotime($vacancy['created_at'])) ?></p>
                            
                            <div class="mt-3">
                                <a href="applications.php?vacancy_id=<?= $vacancy['id'] ?>" class="btn btn-primary btn-sm">
                                    Өтінімдерді қарау
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>