<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'hr') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';

// Получаем офферы текущего HR
$stmt = $pdo->prepare("
    SELECT o.*, r.title as resume_title, r.desired_position,
           u.full_name as jobseeker_name, u.email as jobseeker_email, u.phone as jobseeker_phone
    FROM offers o
    JOIN resumes r ON o.resume_id = r.id
    JOIN users u ON o.job_seeker_id = u.id
    WHERE o.hr_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
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
    <title>Менің офферлерім - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-paper-plane me-2"></i>Менің офферлерім</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (empty($offers)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Сіз әлі оффер жібермедіңіз. <a href="../resumes.php">Резюмелерді қараңыз</a>
            </div>
        <?php else: ?>
            <?php foreach ($offers as $offer): ?>
                <div class="card mb-3">
                    <div class="card-body">
                    <div class="row">
                        <div class="col-md-9">
                            <h5>
                                <?= htmlspecialchars($offer['resume_title']) ?>
                                <span class="badge bg-<?= $statusColors[$offer['status']] ?> ms-2">
                                    <?= $statusTexts[$offer['status']] ?>
                                </span>
                            </h5>
                            
                            <p class="mb-2">
                                <i class="fas fa-user me-2"></i>
                                <strong>Кандидат:</strong> <?= htmlspecialchars($offer['jobseeker_name']) ?>
                            </p>

                            <p class="mb-2">
                                <i class="fas fa-briefcase me-2"></i>
                                <strong>Лауазым:</strong> <?= htmlspecialchars($offer['desired_position']) ?>
                            </p>

                            <?php if ($offer['salary_offer']): ?>
                                <p class="mb-2">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    <strong>Ұсынылған жалақы:</strong> <?= number_format($offer['salary_offer']) ?> ₸
                                </p>
                            <?php endif; ?>

                            <p class="mb-2">
                                <i class="fas fa-envelope me-2"></i>
                                <?= htmlspecialchars($offer['jobseeker_email']) ?>
                            </p>

                            <?php if ($offer['jobseeker_phone']): ?>
                                <p class="mb-2">
                                    <i class="fas fa-phone me-2"></i>
                                    <?= htmlspecialchars($offer['jobseeker_phone']) ?>
                                </p>
                            <?php endif; ?>

                            <div class="cover-letter mt-3">
                                <strong><i class="fas fa-comment me-2"></i>Сіздің хабарламаңыз:</strong>
                                <p class="mt-2"><?= nl2br(htmlspecialchars($offer['message'])) ?></p>
                            </div>

                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Жіберілген: <?= date('d.m.Y H:i', strtotime($offer['created_at'])) ?>
                            </small>
                        </div>

                        <div class="col-md-3 text-end">
                            <?php if ($offer['status'] == 'accepted'): ?>
                                <div class="alert alert-primary">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Кандидат қабылдады!
                                </div>
                            <?php elseif ($offer['status'] == 'rejected'): ?>
                                <div class="alert alert-secondary">
                                    <i class="fas fa-times-circle me-2"></i>
                                    Кандидат қабылдамады
                                </div>
                            <?php else: ?>
                                <div class="alert alert-primary">
                                    <i class="fas fa-clock me-2"></i>
                                    Жауап күтілуде
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2 mt-3">
                                <?php if ($offer['status'] == 'accepted'): ?>
                                    <button onclick="deleteOffer(<?= $offer['id'] ?>)" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-undo me-1"></i>Офферді қайтарып алу
                                    </button>
                                <?php else: ?>
                                    <a href="edit_offer.php?id=<?= $offer['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-edit me-1"></i>Өңдеу
                                    </a>
                                    <button onclick="deleteOffer(<?= $offer['id'] ?>)" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash me-1"></i>Жою
                                    </button>
                                <?php endif; ?>
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
    function deleteOffer(offerId) {
        const confirmMessage = event.target.textContent.includes('қайтарып алу') 
            ? 'Офферді қайтарып алуға сенімдісіз бе? Кандидат хабарландырылады.' 
            : 'Офферді жоюға сенімдісіз бе?';
            
        if (!confirm(confirmMessage)) {
            return;
        }

        fetch('ajax/delete_offer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'offer_id=' + offerId
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
            alert('Қате орын алды!');
            console.error('Error:', error);
        });
    }
    </script>
</body>
</html>
