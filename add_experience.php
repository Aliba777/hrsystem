<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'job_seeker') {
    header("Location: dashboard.php");
    exit;
}

require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $company = $_POST['company'];
        $position = $_POST['position'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'] ?? null;
        $is_current = isset($_POST['is_current']) ? 1 : 0;
        $description = $_POST['description'];

        $stmt = $pdo->prepare("
            INSERT INTO user_experience (user_id, company, position, start_date, end_date, is_current, description)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'], $company, $position, $start_date, 
            $is_current ? null : $end_date, $is_current, $description
        ]);

        $_SESSION['success'] = 'Жұмыс тәжірибесі қосылды!';
        header("Location: profile.php");
        exit;
        
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
    <title>Жұмыс тәжірибесін қосу - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 50px auto;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-handshake me-2"></i>HR Connect
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="profile.php"><i class="fas fa-user me-2"></i>Профиль</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="form-card">
            <h3 class="mb-4"><i class="fas fa-briefcase me-2"></i>Жұмыс тәжірибесін қосу</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Компания атауы *</label>
                    <input type="text" name="company" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Лауазымы *</label>
                    <input type="text" name="position" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Басталған күні *</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Аяқталған күні</label>
                        <input type="date" name="end_date" class="form-control" id="endDate">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_current" id="isCurrent" 
                               onchange="toggleEndDate()">
                        <label class="form-check-label" for="isCurrent">
                            Қазіргі уақытта жұмыс істеймін
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Сипаттама</label>
                    <textarea name="description" class="form-control" rows="5" 
                              placeholder="Міндеттер мен жетістіктер туралы жазыңыз..."></textarea>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Сақтау
                    </button>
                    <a href="profile.php" class="btn btn-secondary btn-lg ms-2">
                        <i class="fas fa-times me-2"></i>Болдырмау
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleEndDate() {
            const checkbox = document.getElementById('isCurrent');
            const endDate = document.getElementById('endDate');
            endDate.disabled = checkbox.checked;
            if (checkbox.checked) {
                endDate.value = '';
            }
        }
    </script>
</body>
</html>
