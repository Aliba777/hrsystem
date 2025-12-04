<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'job_seeker') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';

if ($_POST) {
    $title = $_POST['title'];
    $desired_position = $_POST['desired_position'];
    $desired_salary = $_POST['desired_salary'];
    $work_experience_years = $_POST['work_experience_years'];
    $description = $_POST['description'];
    
    $stmt = $pdo->prepare("INSERT INTO resumes (job_seeker_id, title, desired_position, desired_salary, work_experience_years, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $title, $desired_position, $desired_salary, $work_experience_years, $description]);
    
    header("Location: my_resumes.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Резюме жариялау - HR Connect</title>
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
                    <h2 class="mb-4"><i class="fas fa-file-alt me-2"></i>Резюме жариялау</h2>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Тақырып</label>
                                <input type="text" name="title" class="form-control" placeholder="Мысалы: Junior PHP разработчик іздеймін" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Қалаған лауазым</label>
                                <input type="text" name="desired_position" class="form-control" placeholder="Мысалы: PHP Developer" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Қалаған жалақы (₸)</label>
                                    <input type="number" name="desired_salary" class="form-control" placeholder="300000" min="0">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Жұмыс тәжірибесі (жыл)</label>
                                    <input type="number" name="work_experience_years" class="form-control" placeholder="2" min="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Өзім туралы / Дағдылар</label>
                                <textarea name="description" class="form-control" rows="8" placeholder="Өзіңіз туралы, дағдыларыңыз, тәжірибеңіз туралы жазыңыз..." required></textarea>
                            </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Резюме жариялау
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
