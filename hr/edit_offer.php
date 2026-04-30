<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'hr') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';

$offer_id = $_GET['id'] ?? 0;

// Получаем оффер с проверкой, что он принадлежит текущему HR
$stmt = $pdo->prepare("
    SELECT o.*, r.title as resume_title, r.desired_position,
           u.full_name as jobseeker_name
    FROM offers o
    JOIN resumes r ON o.resume_id = r.id
    JOIN users u ON o.job_seeker_id = u.id
    WHERE o.id = ? AND o.hr_id = ?
");
$stmt->execute([$offer_id, $_SESSION['user_id']]);
$offer = $stmt->fetch();

if (!$offer) {
    header("Location: my_offers.php");
    exit;
}

// Запрещаем редактирование принятых офферов
if ($offer['status'] == 'accepted') {
    $_SESSION['error'] = "Қабылданған офферді өңдеуге болмайды!";
    header("Location: my_offers.php");
    exit;
}

// Обработка обновления оффера
if ($_POST) {
    $message = $_POST['message'];
    $salary_offer = $_POST['salary_offer'] ?? null;
    
    $stmt = $pdo->prepare("UPDATE offers SET message = ?, salary_offer = ? WHERE id = ?");
    if ($stmt->execute([$message, $salary_offer, $offer_id])) {
        $success = "Оффер сәтті жаңартылды!";
        // Обновляем данные
        $stmt = $pdo->prepare("
            SELECT o.*, r.title as resume_title, r.desired_position,
                   u.full_name as jobseeker_name
            FROM offers o
            JOIN resumes r ON o.resume_id = r.id
            JOIN users u ON o.job_seeker_id = u.id
            WHERE o.id = ? AND o.hr_id = ?
        ");
        $stmt->execute([$offer_id, $_SESSION['user_id']]);
        $offer = $stmt->fetch();
    } else {
        $error = "Қате орын алды!";
    }
}

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
    <title>Офферді өңдеу - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Офферді өңдеу</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($offer['status'] == 'accepted'): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Қабылданған офферді өңдеуге болмайды!
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h5><?= htmlspecialchars($offer['resume_title']) ?></h5>
                            <p class="mb-1">
                                <i class="fas fa-user me-2"></i>
                                <strong>Кандидат:</strong> <?= htmlspecialchars($offer['jobseeker_name']) ?>
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-briefcase me-2"></i>
                                <strong>Лауазым:</strong> <?= htmlspecialchars($offer['desired_position']) ?>
                            </p>
                            <p class="mb-0">
                                <strong>Статус:</strong> 
                                <span class="badge bg-primary"><?= $statusTexts[$offer['status']] ?></span>
                            </p>
                        </div>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Ұсынылатын жалақы (₸)</label>
                                <input type="number" name="salary_offer" class="form-control" 
                                       placeholder="350000" min="0"
                                       value="<?= $offer['salary_offer'] ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Хабарлама *</label>
                                <textarea name="message" class="form-control" rows="8" 
                                          required><?= htmlspecialchars($offer['message']) ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Сақтау
                                </button>
                                <a href="my_offers.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Артқа
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
