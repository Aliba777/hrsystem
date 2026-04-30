<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';

// Получаем резюме пользователя
$stmt = $pdo->prepare("
    SELECT r.*, 
           (SELECT COUNT(*) FROM offers WHERE resume_id = r.id) as offers_count
    FROM resumes r
    WHERE r.job_seeker_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$resumes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Менің резюмелерім - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-alt me-2"></i>Менің резюмелерім</h2>
            <a href="post_resume.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Жаңа резюме қосу
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>Резюме сәтті жарияланды!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($resumes)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Сізде әлі резюме жоқ. Жаңа резюме жариялаңыз!
            </div>
        <?php else: ?>
            <?php foreach ($resumes as $resume): ?>
                <div class="card mb-3">
                    <div class="card-body">
                    <div class="row">
                        <div class="col-md-9">
                            <h4><?= htmlspecialchars($resume['title']) ?></h4>
                            <p class="mb-2">
                                <i class="fas fa-briefcase me-2"></i>
                                <strong>Лауазым:</strong> <?= htmlspecialchars($resume['desired_position']) ?>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                <strong>Қалаған жалақы:</strong> <?= number_format($resume['desired_salary']) ?> ₸
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Тәжірибе:</strong> <?= $resume['work_experience_years'] ?> жыл
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-envelope me-2"></i>
                                <strong>Офферлер:</strong> 
                                <span class="badge bg-primary"><?= $resume['offers_count'] ?></span>
                            </p>
                            <p class="text-muted mb-2">
                                <?= nl2br(htmlspecialchars(substr($resume['description'], 0, 200))) ?>...
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Жарияланған: <?= date('d.m.Y H:i', strtotime($resume['created_at'])) ?>
                            </small>
                        </div>
                        <div class="col-md-3 text-end">
                            <span class="badge bg-<?= $resume['status'] == 'active' ? 'primary' : 'secondary' ?> mb-2">
                                <?= $resume['status'] == 'active' ? 'Белсенді' : 'Белсенді емес' ?>
                            </span>
                            <div class="d-grid gap-2">
                                <a href="resume_offers.php?id=<?= $resume['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-envelope me-1"></i>Офферлер (<?= $resume['offers_count'] ?>)
                                </a>
                                <a href="edit_resume.php?id=<?= $resume['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>Өңдеу
                                </a>
                                <button onclick="deleteResume(<?= $resume['id'] ?>)" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash me-1"></i>Жою
                                </button>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteResume(resumeId) {
            if (confirm('Бұл резюмені жою керек пе?')) {
                fetch('ajax/delete_resume.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `resume_id=${resumeId}`
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
