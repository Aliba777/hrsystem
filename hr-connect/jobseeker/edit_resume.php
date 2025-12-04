<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';

$resume_id = $_GET['id'] ?? 0;

// Проверяем что резюме принадлежит текущему пользователю
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ? AND job_seeker_id = ?");
$stmt->execute([$resume_id, $_SESSION['user_id']]);
$resume = $stmt->fetch();

if (!$resume) {
    header("Location: my_resumes.php");
    exit;
}

if ($_POST) {
    $title = $_POST['title'];
    $desired_position = $_POST['desired_position'];
    $desired_salary = $_POST['desired_salary'];
    $work_experience_years = $_POST['work_experience_years'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE resumes SET title = ?, desired_position = ?, desired_salary = ?, work_experience_years = ?, description = ?, status = ? WHERE id = ? AND job_seeker_id = ?");
    $stmt->execute([$title, $desired_position, $desired_salary, $work_experience_years, $description, $status, $resume_id, $_SESSION['user_id']]);
    
    header("Location: my_resumes.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Резюмені өңдеу - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-card">
                    <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Резюмені өңдеу</h2>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Тақырып</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($resume['title']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Қалаған лауазым</label>
                                <input type="text" name="desired_position" class="form-control" value="<?= htmlspecialchars($resume['desired_position']) ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Қалаған жалақы (₸)</label>
                                    <input type="number" name="desired_salary" class="form-control" value="<?= $resume['desired_salary'] ?>" min="0">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Жұмыс тәжірибесі (жыл)</label>
                                    <input type="number" name="work_experience_years" class="form-control" value="<?= $resume['work_experience_years'] ?>" min="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Өзім туралы / Дағдылар</label>
                                <textarea name="description" class="form-control" rows="8" required><?= htmlspecialchars($resume['description']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Статус</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $resume['status'] == 'active' ? 'selected' : '' ?>>Белсенді</option>
                                    <option value="inactive" <?= $resume['status'] == 'inactive' ? 'selected' : '' ?>>Белсенді емес</option>
                                </select>
                            </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Сақтау
                            </button>
                            <a href="my_resumes.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Артқа
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
