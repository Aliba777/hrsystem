<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';

$resume_id = $_GET['id'] ?? 0;

// Проверяем что резюме принадлежит текущему пользователю
$check = $pdo->prepare("SELECT * FROM resumes WHERE id = ? AND job_seeker_id = ?");
$check->execute([$resume_id, $_SESSION['user_id']]);
$resume = $check->fetch();

if (!$resume) {
    header("Location: my_resumes.php");
    exit;
}

// Получаем офферы
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name as hr_name, u.email as hr_email, u.phone as hr_phone
    FROM offers o
    JOIN users u ON o.hr_id = u.id
    WHERE o.resume_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$resume_id]);
$offers = $stmt->fetchAll();

$statusColors = [
    'pending' => 'primary',
    'accepted' => 'primary',
    'rejected' => 'secondary'
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
    <title>Офферлер - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-envelope me-2"></i>Офферлер</h2>
                <p class="text-muted">Резюме: <?= htmlspecialchars($resume['title']) ?></p>
            </div>
            <a href="my_resumes.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Артқа
            </a>
        </div>

        <?php if (empty($offers)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Әлі офферлер жоқ
            </div>
        <?php else: ?>
            <?php foreach ($offers as $offer): ?>
                <div class="card mb-3">
                    <div class="card-body">
                    <div class="row">
                        <div class="col-md-9">
                            <h5>
                                <i class="fas fa-building me-2"></i><?= htmlspecialchars($offer['hr_name']) ?>
                                <span class="badge bg-<?= $statusColors[$offer['status']] ?> ms-2">
                                    <?= $statusTexts[$offer['status']] ?>
                                </span>
                            </h5>
                            
                            <?php if ($offer['salary_offer']): ?>
                                <p class="mb-2">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    <strong>Ұсынылған жалақы:</strong> <?= number_format($offer['salary_offer']) ?> ₸
                                </p>
                            <?php endif; ?>

                            <p class="mb-2">
                                <i class="fas fa-envelope me-2"></i>
                                <strong>Email:</strong> <?= htmlspecialchars($offer['hr_email']) ?>
                            </p>

                            <?php if ($offer['hr_phone']): ?>
                                <p class="mb-2">
                                    <i class="fas fa-phone me-2"></i>
                                    <strong>Телефон:</strong> <?= htmlspecialchars($offer['hr_phone']) ?>
                                </p>
                            <?php endif; ?>

                            <div class="cover-letter mt-3">
                                <strong><i class="fas fa-comment me-2"></i>Хабарлама:</strong>
                                <p class="mt-2"><?= nl2br(htmlspecialchars($offer['message'])) ?></p>
                            </div>

                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Жіберілген: <?= date('d.m.Y H:i', strtotime($offer['created_at'])) ?>
                            </small>
                        </div>

                        <div class="col-md-3 text-end">
                            <?php if ($offer['status'] == 'pending'): ?>
                                <div class="d-grid gap-2">
                                    <button onclick="updateOfferStatus(<?= $offer['id'] ?>, 'accepted')" class="btn btn-primary">
                                        <i class="fas fa-check me-1"></i>Қабылдау
                                    </button>
                                    <button onclick="updateOfferStatus(<?= $offer['id'] ?>, 'rejected')" class="btn btn-outline-danger">
                                        <i class="fas fa-times me-1"></i>Қабылдамау
                                    </button>
                                </div>
                            <?php endif; ?>
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
        function updateOfferStatus(offerId, status) {
            const statusText = status === 'accepted' ? 'қабылдау' : 'қабылдамау';
            
            if (confirm(`Бұл офферді ${statusText} керек пе?`)) {
                fetch('ajax/update_offer_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `offer_id=${offerId}&status=${status}`
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
