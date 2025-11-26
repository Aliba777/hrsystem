<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Основная информация
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $bio = $_POST['bio'];
        $birth_date = $_POST['birth_date'];
        $gender = $_POST['gender'];
        $city = $_POST['city'];
        $address = $_POST['address'];
        $website = $_POST['website'];
        $linkedin = $_POST['linkedin'];
        $telegram = $_POST['telegram'];
        $whatsapp = $_POST['whatsapp'];

        // Обновляем основную информацию
        $update_stmt = $pdo->prepare("
            UPDATE users SET 
                full_name = ?, email = ?, phone = ?, bio = ?, 
                birth_date = ?, gender = ?, city = ?, address = ?,
                website = ?, linkedin = ?, telegram = ?, whatsapp = ?
            WHERE id = ?
        ");
        
        $update_stmt->execute([
            $full_name, $email, $phone, $bio,
            $birth_date ?: null, $gender ?: null, $city, $address,
            $website, $linkedin, $telegram, $whatsapp,
            $_SESSION['user_id']
        ]);

        // Обновляем навыки (только для соискателей)
        if ($_SESSION['user_type'] == 'job_seeker' && isset($_POST['skills'])) {
            // Удаляем старые навыки
            $pdo->prepare("DELETE FROM user_skills WHERE user_id = ?")->execute([$_SESSION['user_id']]);
            
            // Добавляем новые
            $skills = array_filter(array_map('trim', explode(',', $_POST['skills'])));
            $skill_stmt = $pdo->prepare("INSERT INTO user_skills (user_id, skill_name) VALUES (?, ?)");
            foreach ($skills as $skill) {
                if (!empty($skill)) {
                    $skill_stmt->execute([$_SESSION['user_id'], $skill]);
                }
            }
        }

        // Обновляем языки
        if (isset($_POST['languages']) && is_array($_POST['languages'])) {
            $pdo->prepare("DELETE FROM user_languages WHERE user_id = ?")->execute([$_SESSION['user_id']]);
            
            $lang_stmt = $pdo->prepare("INSERT INTO user_languages (user_id, language, proficiency) VALUES (?, ?, ?)");
            foreach ($_POST['languages'] as $index => $language) {
                if (!empty($language) && !empty($_POST['proficiency'][$index])) {
                    $lang_stmt->execute([$_SESSION['user_id'], $language, $_POST['proficiency'][$index]]);
                }
            }
        }

        $_SESSION['success'] = 'Профиль сәтті жаңартылды!';
        header("Location: profile.php");
        exit;
        
    } catch (PDOException $e) {
        $error = 'Қате: ' . $e->getMessage();
    }
}

// Получаем навыки
$skills_stmt = $pdo->prepare("SELECT skill_name FROM user_skills WHERE user_id = ?");
$skills_stmt->execute([$_SESSION['user_id']]);
$skills = $skills_stmt->fetchAll(PDO::FETCH_COLUMN);

// Получаем языки
$lang_stmt = $pdo->prepare("SELECT * FROM user_languages WHERE user_id = ?");
$lang_stmt->execute([$_SESSION['user_id']]);
$languages = $lang_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профильді өңдеу - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .edit-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .edit-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .edit-card h4 {
            color: #667eea;
            margin-bottom: 25px;
            font-weight: 600;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .language-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .btn-add-language {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            margin-top: 10px;
        }
        .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 15px;
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
                <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i>Басты бет</a>
            </div>
        </div>
    </nav>

    <div class="edit-header">
        <div class="container">
            <h2><i class="fas fa-edit me-2"></i>Профильді өңдеу</h2>
            <p class="mb-0">Өзіңіз туралы ақпаратты толтырыңыз</p>
        </div>
    </div>

    <div class="container mb-5">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- Основная информация -->
            <div class="edit-card">
                <h4><i class="fas fa-user me-2"></i>Негізгі ақпарат</h4>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Толық аты-жөні *</label>
                        <input type="text" name="full_name" class="form-control" 
                               value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Телефон</label>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                               placeholder="+7 (777) 123-45-67">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Туған күні</label>
                        <input type="date" name="birth_date" class="form-control" 
                               value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Жынысы</label>
                        <select name="gender" class="form-control">
                            <option value="">Таңдаңыз</option>
                            <option value="male" <?= $user['gender'] == 'male' ? 'selected' : '' ?>>Ер</option>
                            <option value="female" <?= $user['gender'] == 'female' ? 'selected' : '' ?>>Әйел</option>
                            <option value="other" <?= $user['gender'] == 'other' ? 'selected' : '' ?>>Басқа</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Қала</label>
                        <input type="text" name="city" class="form-control" 
                               value="<?= htmlspecialchars($user['city'] ?? '') ?>" 
                               placeholder="Алматы">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Мекен-жайы</label>
                    <input type="text" name="address" class="form-control" 
                           value="<?= htmlspecialchars($user['address'] ?? '') ?>" 
                           placeholder="Көше, үй">
                </div>

                <div class="mb-3">
                    <label class="form-label">Өзім туралы</label>
                    <textarea name="bio" class="form-control" rows="4" 
                              placeholder="Өзіңіз туралы қысқаша жазыңыз..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Контакты -->
            <div class="edit-card">
                <h4><i class="fas fa-link me-2"></i>Байланыс және әлеуметтік желілер</h4>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-globe me-2"></i>Веб-сайт</label>
                        <input type="url" name="website" class="form-control" 
                               value="<?= htmlspecialchars($user['website'] ?? '') ?>" 
                               placeholder="https://example.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fab fa-linkedin me-2"></i>LinkedIn</label>
                        <input type="url" name="linkedin" class="form-control" 
                               value="<?= htmlspecialchars($user['linkedin'] ?? '') ?>" 
                               placeholder="https://linkedin.com/in/username">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fab fa-telegram me-2"></i>Telegram</label>
                        <input type="text" name="telegram" class="form-control" 
                               value="<?= htmlspecialchars($user['telegram'] ?? '') ?>" 
                               placeholder="@username">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fab fa-whatsapp me-2"></i>WhatsApp</label>
                        <input type="text" name="whatsapp" class="form-control" 
                               value="<?= htmlspecialchars($user['whatsapp'] ?? '') ?>" 
                               placeholder="+7 777 123 45 67">
                    </div>
                </div>
            </div>

            <?php if ($_SESSION['user_type'] == 'job_seeker'): ?>
            <!-- Навыки -->
            <div class="edit-card">
                <h4><i class="fas fa-star me-2"></i>Дағдылар</h4>
                <div class="mb-3">
                    <label class="form-label">Дағдыларыңызды үтірмен бөліп жазыңыз</label>
                    <input type="text" name="skills" class="form-control" 
                           value="<?= htmlspecialchars(implode(', ', $skills)) ?>" 
                           placeholder="PHP, JavaScript, Python, SQL">
                    <small class="text-muted">Мысалы: PHP, JavaScript, Python, SQL, HTML, CSS</small>
                </div>
            </div>
            <?php endif; ?>

            <!-- Языки -->
            <div class="edit-card">
                <h4><i class="fas fa-language me-2"></i>Тілдер</h4>
                <div id="languagesContainer">
                    <?php if (empty($languages)): ?>
                        <div class="language-row">
                            <input type="text" name="languages[]" class="form-control" placeholder="Тіл атауы">
                            <select name="proficiency[]" class="form-control">
                                <option value="basic">Базалық</option>
                                <option value="intermediate" selected>Орташа</option>
                                <option value="advanced">Жоғары</option>
                                <option value="native">Ана тілі</option>
                            </select>
                            <button type="button" class="btn btn-remove" onclick="removeLanguage(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($languages as $lang): ?>
                            <div class="language-row">
                                <input type="text" name="languages[]" class="form-control" 
                                       value="<?= htmlspecialchars($lang['language']) ?>" placeholder="Тіл атауы">
                                <select name="proficiency[]" class="form-control">
                                    <option value="basic" <?= $lang['proficiency'] == 'basic' ? 'selected' : '' ?>>Базалық</option>
                                    <option value="intermediate" <?= $lang['proficiency'] == 'intermediate' ? 'selected' : '' ?>>Орташа</option>
                                    <option value="advanced" <?= $lang['proficiency'] == 'advanced' ? 'selected' : '' ?>>Жоғары</option>
                                    <option value="native" <?= $lang['proficiency'] == 'native' ? 'selected' : '' ?>>Ана тілі</option>
                                </select>
                                <button type="button" class="btn btn-remove" onclick="removeLanguage(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-add-language" onclick="addLanguage()">
                    <i class="fas fa-plus me-2"></i>Тіл қосу
                </button>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-edit btn-lg">
                    <i class="fas fa-save me-2"></i>Сақтау
                </button>
                <a href="profile.php" class="btn btn-secondary btn-lg ms-2">
                    <i class="fas fa-times me-2"></i>Болдырмау
                </a>
            </div>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addLanguage() {
            const container = document.getElementById('languagesContainer');
            const row = document.createElement('div');
            row.className = 'language-row';
            row.innerHTML = `
                <input type="text" name="languages[]" class="form-control" placeholder="Тіл атауы">
                <select name="proficiency[]" class="form-control">
                    <option value="basic">Базалық</option>
                    <option value="intermediate" selected>Орташа</option>
                    <option value="advanced">Жоғары</option>
                    <option value="native">Ана тілі</option>
                </select>
                <button type="button" class="btn btn-remove" onclick="removeLanguage(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(row);
        }

        function removeLanguage(button) {
            button.parentElement.remove();
        }
    </script>
</body>
</html>
