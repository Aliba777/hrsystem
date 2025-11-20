<?php
session_start();
if ($_SESSION['user_type'] != 'hr') {
    header("Location: ../dashboard.php");
    exit;
}

require_once '../config/database.php';

$success = '';
$error = '';

if ($_POST) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $salary = $_POST['salary'];
    $address = $_POST['address'];
    $hr_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO vacancies (hr_id, title, description, salary, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$hr_id, $title, $description, $salary, $address]);
        
        $success = "Вакансия успешно опубликована!";
        // Очищаем форму после успешной отправки
        $_POST = array();
    } catch (PDOException $e) {
        $error = "Ошибка при публикации вакансии: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вакансия қосу - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --success: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 30px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .vacancy-container {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            padding: 40px 0;
        }

        .vacancy-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: var(--gradient);
            color: white;
            padding: 30px;
            border-bottom: none;
            text-align: center;
        }

        .card-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .card-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .card-body {
            padding: 40px;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .input-group-text {
            background: var(--primary);
            border: none;
            color: white;
            border-radius: 12px 0 0 12px;
        }

        .btn-primary {
            background: var(--gradient);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
        }

        .feature-list {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .feature-list h6 {
            color: var(--primary);
            margin-bottom: 15px;
        }

        .feature-list ul {
            margin: 0;
            padding-left: 20px;
        }

        .feature-list li {
            margin-bottom: 8px;
            color: #666;
        }

        .character-count {
            text-align: right;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .salary-preview {
            background: linear-gradient(135deg, #4cc9f0, #4361ee);
            color: white;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            margin-top: 10px;
        }

        .salary-preview h5 {
            margin: 0;
            font-weight: 700;
        }

        .alert-success {
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .alert-danger {
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #dc3545, #e83e8c);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-handshake me-2"></i>HR Connect
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="my_vacancies.php"><i class="fas fa-list me-1"></i>Менің вакансияларым</a>
                <a class="nav-link" href="../dashboard.php"><i class="fas fa-home me-1"></i>Басты бет</a>
                <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-1"></i>Шығу</a>
            </div>
        </div>
    </nav>

    <div class="vacancy-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="vacancy-card">
                        <div class="card-header">
                            <h2><i class="fas fa-plus-circle me-2"></i>Жаңа вакансия қосу</h2>
                            <p>Бос жұмыс орнын толық сипаттаумен толтырыңыз</p>
                        </div>
                        
                        <div class="card-body">
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?= $success ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-lg-8">
                                    <form method="POST" id="vacancyForm">
                                        <div class="mb-4">
                                            <label class="form-label">Вакансия атауы *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                                <input type="text" name="title" class="form-control" 
                                                       placeholder="Мысалы: Frontend әзірлеушісі" 
                                                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label">Сипаттама *</label>
                                            <textarea name="description" class="form-control" rows="8" 
                                                      placeholder="Жұмыс сипаттамасын, міндеттерді, талаптарды егжей-тегжейлі сипаттаңыз..."
                                                      oninput="updateCharacterCount(this)"
                                                      required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                            <div class="character-count">
                                                <span id="charCount">0</span> таңба
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label">Жалақы (₸) *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                                        <input type="number" name="salary" class="form-control" 
                                                               placeholder="500000" 
                                                               value="<?= htmlspecialchars($_POST['salary'] ?? '') ?>" 
                                                               oninput="updateSalaryPreview(this.value)"
                                                               required>
                                                    </div>
                                                    <div class="salary-preview" id="salaryPreview" style="display: none;">
                                                        <h5><i class="fas fa-eye me-2"></i><span id="previewAmount">0</span> ₸</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label">Жұмыс орны *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                                        <input type="text" name="address" class="form-control" 
                                                               placeholder="Мысалы: Нұр-Сұлтан, Достык көш. 12" 
                                                               value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-2"></i>Вакансияны жариялау
                                        </button>
                                    </form>
                                </div>

                                <div class="col-lg-4">
                                    <div class="feature-list">
                                        <h6><i class="fas fa-lightbulb me-2"></i>Кеңес беру</h6>
                                        <ul>
                                            <li>Атауды нақты және тартымды етіп жазыңыз</li>
                                            <li>Сипаттаманы толық және түсінікті етіп жазыңыз</li>
                                            <li>Жалақыны нақты көрсетіңіз</li>
                                            <li>Жұмыс орнын нақты көрсетіңіз</li>
                                            <li>Барлық міндеттерді тізіп жазыңыз</li>
                                        </ul>
                                    </div>

                                    <div class="feature-list">
                                        <h6><i class="fas fa-chart-line me-2"></i>Статистика</h6>
                                        <ul>
                                            <li>Толық сипаттамалы вакансиялар 40% артық қызығушылық тудырады</li>
                                            <li>Жалақыны көрсету қабылдау санын 60% арттырады</li>
                                            <li>Орналастырғаннан кейін бірінші 24 сағатта ең көп қаралым болады</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Есімдік санағыш
        function updateCharacterCount(textarea) {
            const count = textarea.value.length;
            document.getElementById('charCount').textContent = count;
        }

        // Жалақы алдын ала қарау
        function updateSalaryPreview(value) {
            const preview = document.getElementById('salaryPreview');
            const amount = document.getElementById('previewAmount');
            
            if (value && value > 0) {
                amount.textContent = new Intl.NumberFormat('kz-KZ').format(value);
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }

        // Форманы тексеру
        document.getElementById('vacancyForm').addEventListener('submit', function(e) {
            const title = document.querySelector('input[name="title"]').value;
            const description = document.querySelector('textarea[name="description"]').value;
            const salary = document.querySelector('input[name="salary"]').value;
            const address = document.querySelector('input[name="address"]').value;

            if (!title || !description || !salary || !address) {
                e.preventDefault();
                alert('Барлық міндетті өрістерді толтырыңыз!');
                return false;
            }

            if (description.length < 50) {
                e.preventDefault();
                alert('Сипаттама тым қысқа! Кемінде 50 таңбадан тұруы керек.');
                return false;
            }
        });

        // Бастапқы есімдік санын есептеу
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.querySelector('textarea[name="description"]');
            updateCharacterCount(textarea);
            
            const salaryInput = document.querySelector('input[name="salary"]');
            updateSalaryPreview(salaryInput.value);
        });
    </script>
</body>
</html>