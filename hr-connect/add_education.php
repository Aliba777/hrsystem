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
        $institution = $_POST['institution'];
        $degree = $_POST['degree'];
        $field_of_study = $_POST['field_of_study'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $description = $_POST['description'];

        $stmt = $pdo->prepare("
            INSERT INTO user_education (user_id, institution, degree, field_of_study, start_date, end_date, description)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'], $institution, $degree, $field_of_study,
            $start_date, $end_date, $description
        ]);

        $_SESSION['success'] = 'Білім туралы ақпарат қосылды!';
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
    <title>Білім қосу - HR Connect</title>
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
            <h3 class="mb-4"><i class="fas fa-graduation-cap me-2"></i>Білім қосу</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Оқу орны *</label>
                    <input type="text" name="institution" class="form-control" 
                           placeholder="Университет, колледж атауы" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Дәреже *</label>
                        <select name="degree" class="form-control" required>
                            <option value="">Таңдаңыз</option>
                            <option value="Бакалавр">Бакалавр</option>
                            <option value="Магистр">Магистр</option>
                            <option value="Доктор">Доктор (PhD)</option>
                            <option value="Колледж">Колледж</option>
                            <option value="Орта білім">Орта білім</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Мамандық</label>
                        <input type="text" name="field_of_study" class="form-control" 
                               placeholder="Информатика, Экономика...">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Басталған жылы *</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Аяқталған жылы *</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Қосымша ақпарат</label>
                    <textarea name="description" class="form-control" rows="4" 
                              placeholder="Жетістіктер, дипломдар, сертификаттар..."></textarea>
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
</body>
</html>
