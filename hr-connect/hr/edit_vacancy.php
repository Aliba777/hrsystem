<?php
session_start();
if ($_SESSION['user_type'] != 'hr') {
    header("Location: ../dashboard.php");
    exit;
}

require_once '../config/database.php';

$vacancy_id = $_GET['id'] ?? 0;

// Получаем вакансию
$stmt = $pdo->prepare("SELECT * FROM vacancies WHERE id = ? AND hr_id = ?");
$stmt->execute([$vacancy_id, $_SESSION['user_id']]);
$vacancy = $stmt->fetch();

if (!$vacancy) {
    header("Location: my_vacancies.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $salary = $_POST['salary'];
        $address = $_POST['address'];
        
        $update = $pdo->prepare("
            UPDATE vacancies 
            SET title = ?, description = ?, salary = ?, address = ?
            WHERE id = ? AND hr_id = ?
        ");
        
        $update->execute([$title, $description, $salary, $address, $vacancy_id, $_SESSION['user_id']]);
        
        $success = 'Вакансия сәтті жаңартылды!';
        
        // Обновляем данные
        $stmt->execute([$vacancy_id, $_SESSION['user_id']]);
        $vacancy = $stmt->fetch();
        
    } catch (PDOException $e) {
        $error = 'Қате: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вакансияны өңдеу - HR Connect</title>
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
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-handshake me-2"></i>HR Connect
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="my_vacancies.php"><i class="fas fa-briefcase me-2"></i>Менің вакансияларым</a>
                <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Шығу</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-card">
                    <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Вакансияны өңдеу</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Лауазым атауы *</label>
                            <input type="text" name="title" class="form-control" 
                                   value="<?= htmlspecialchars($vacancy['title']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Сипаттама *</label>
                            <textarea name="description" class="form-control" rows="6" required><?= htmlspecialchars($vacancy['description']) ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Жалақы (₸) *</label>
                            <input type="number" name="salary" class="form-control" 
                                   value="<?= $vacancy['salary'] ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Мекен-жайы *</label>
                            <input type="text" name="address" class="form-control" 
                                   value="<?= htmlspecialchars($vacancy['address']) ?>" required>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Сақтау
                            </button>
                            <a href="my_vacancies.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Болдырмау
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
