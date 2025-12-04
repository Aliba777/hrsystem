<?php
session_start();

// Только HR может просматривать все резюме
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'hr') {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// Получаем все активные резюме
$search = $_GET['search'] ?? '';
$sql = "
    SELECT r.*, u.full_name, u.email, u.phone, u.city
    FROM resumes r
    JOIN users u ON r.job_seeker_id = u.id
    WHERE r.status = 'active'
";

if ($search) {
    $sql .= " AND (r.title LIKE ? OR r.desired_position LIKE ? OR r.description LIKE ?)";
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($sql);

if ($search) {
    $searchParam = "%$search%";
    $stmt->execute([$searchParam, $searchParam, $searchParam]);
} else {
    $stmt->execute();
}

$resumes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Резюмелер - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-users me-2"></i>Резюмелер</h2>

        <!-- Поиск -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Лауазым, дағдылар бойынша іздеу..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Іздеу
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($resumes)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Резюмелер табылмады
            </div>
        <?php else: ?>
            <?php foreach ($resumes as $resume): ?>
                <div class="card mb-3">
                    <div class="card-body">
                    <div class="row">
                        <div class="col-md-9">
                            <h4><?= htmlspecialchars($resume['title']) ?></h4>
                            <p class="mb-2">
                                <i class="fas fa-user me-2"></i>
                                <strong><?= htmlspecialchars($resume['full_name']) ?></strong>
                            </p>
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
                            <?php if ($resume['city']): ?>
                                <p class="mb-2">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <?= htmlspecialchars($resume['city']) ?>
                                </p>
                            <?php endif; ?>
                            <p class="text-muted mb-2">
                                <?= nl2br(htmlspecialchars(substr($resume['description'], 0, 200))) ?>...
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('d.m.Y', strtotime($resume['created_at'])) ?>
                            </small>
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="resume_detail.php?id=<?= $resume['id'] ?>" class="btn btn-primary w-100">
                                <i class="fas fa-eye me-2"></i>Толығырақ
                            </a>
                        </div>
                    </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
