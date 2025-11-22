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

// Получаем образование
$edu_stmt = $pdo->prepare("SELECT * FROM user_education WHERE user_id = ? ORDER BY start_date DESC");
$edu_stmt->execute([$_SESSION['user_id']]);
$education = $edu_stmt->fetchAll();

// Получаем опыт работы
$exp_stmt = $pdo->prepare("SELECT * FROM user_experience WHERE user_id = ? ORDER BY start_date DESC");
$exp_stmt->execute([$_SESSION['user_id']]);
$experience = $exp_stmt->fetchAll();

// Получаем навыки
$skills_stmt = $pdo->prepare("SELECT * FROM user_skills WHERE user_id = ?");
$skills_stmt->execute([$_SESSION['user_id']]);
$skills = $skills_stmt->fetchAll();

// Получаем языки
$lang_stmt = $pdo->prepare("SELECT * FROM user_languages WHERE user_id = ?");
$lang_stmt->execute([$_SESSION['user_id']]);
$languages = $lang_stmt->fetchAll();

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .profile-card h4 {
            color: #667eea;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .info-item i {
            width: 30px;
            color: #667eea;
        }
        .skill-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            margin: 5px;
            font-size: 0.9rem;
        }
        .timeline-item {
            border-left: 3px solid #667eea;
            padding-left: 20px;
            margin-bottom: 25px;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            width: 15px;
            height: 15px;
            background: #667eea;
            border-radius: 50%;
            position: absolute;
            left: -9px;
            top: 0;
        }
        .btn-edit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
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
                <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i>Басты бет</a>
                <?php if ($_SESSION['user_type'] == 'job_seeker'): ?>
                    <a class="nav-link" href="jobseeker/browse_vacancies.php"><i class="fas fa-search me-2"></i>Вакансиялар</a>
                <?php else: ?>
                    <a class="nav-link" href="hr/my_vacancies.php"><i class="fas fa-briefcase me-2"></i>Вакансиялар</a>
                <?php endif; ?>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Шығу</a>
            </div>
        </div>
    </nav>

    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img src="<?= $user['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name']) . '&size=150&background=667eea&color=fff' ?>" 
                         alt="Avatar" class="profile-avatar">
                </div>
                <div class="col-md-6">
                    <h2><?= htmlspecialchars($user['full_name']) ?></h2>
                    <p class="mb-2"><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($user['email']) ?></p>
                    <?php if ($user['phone']): ?>
                        <p class="mb-2"><i class="fas fa-phone me-2"></i><?= htmlspecialchars($user['phone']) ?></p>
                    <?php endif; ?>
                    <span class="badge bg-light text-dark">
                        <?= $user['user_type'] == 'job_seeker' ? 'Жұмыс іздеуші' : 'HR менеджер' ?>
                    </span>
                </div>
                <div class="col-md-3 text-end">
                    <a href="edit_profile.php" class="btn btn-light">
                        <i class="fas fa-edit me-2"></i>Профильді өңдеу
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <!-- Основная информация -->
                <div class="profile-card">
                    <h4><i class="fas fa-user me-2"></i>Жеке ақпарат</h4>
                    
                    <?php if ($user['bio']): ?>
                        <div class="info-item">
                            <i class="fas fa-quote-left"></i>
                            <span><?= nl2br(htmlspecialchars($user['bio'])) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($user['birth_date']): ?>
                        <div class="info-item">
                            <i class="fas fa-birthday-cake"></i>
                            <span><?= date('d.m.Y', strtotime($user['birth_date'])) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($user['city']): ?>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($user['city']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($user['website']): ?>
                        <div class="info-item">
                            <i class="fas fa-globe"></i>
                            <a href="<?= htmlspecialchars($user['website']) ?>" target="_blank">Веб-сайт</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Контакты -->
                <div class="profile-card">
                    <h4><i class="fas fa-address-book me-2"></i>Байланыс</h4>
                    
                    <?php if ($user['linkedin']): ?>
                        <div class="info-item">
                            <i class="fab fa-linkedin"></i>
                            <a href="<?= htmlspecialchars($user['linkedin']) ?>" target="_blank">LinkedIn</a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($user['telegram']): ?>
                        <div class="info-item">
                            <i class="fab fa-telegram"></i>
                            <span><?= htmlspecialchars($user['telegram']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($user['whatsapp']): ?>
                        <div class="info-item">
                            <i class="fab fa-whatsapp"></i>
                            <span><?= htmlspecialchars($user['whatsapp']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Языки -->
                <?php if (!empty($languages)): ?>
                <div class="profile-card">
                    <h4><i class="fas fa-language me-2"></i>Тілдер</h4>
                    <?php foreach ($languages as $lang): ?>
                        <div class="info-item">
                            <i class="fas fa-check-circle"></i>
                            <span><?= htmlspecialchars($lang['language']) ?> - 
                                <?php
                                $levels = ['basic' => 'Базалық', 'intermediate' => 'Орташа', 'advanced' => 'Жоғары', 'native' => 'Ана тілі'];
                                echo $levels[$lang['proficiency']];
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-md-8">
                <?php if ($_SESSION['user_type'] == 'job_seeker'): ?>
                    <!-- Навыки -->
                    <?php if (!empty($skills)): ?>
                    <div class="profile-card">
                        <h4><i class="fas fa-star me-2"></i>Дағдылар</h4>
                        <div>
                            <?php foreach ($skills as $skill): ?>
                                <span class="skill-badge">
                                    <?= htmlspecialchars($skill['skill_name']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Опыт работы -->
                    <?php if (!empty($experience)): ?>
                    <div class="profile-card">
                        <h4><i class="fas fa-briefcase me-2"></i>Жұмыс тәжірибесі</h4>
                        <?php foreach ($experience as $exp): ?>
                            <div class="timeline-item">
                                <h5><?= htmlspecialchars($exp['position']) ?></h5>
                                <p class="text-muted mb-1">
                                    <strong><?= htmlspecialchars($exp['company']) ?></strong>
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-calendar me-2"></i>
                                    <?= date('m.Y', strtotime($exp['start_date'])) ?> - 
                                    <?= $exp['is_current'] ? 'Қазіргі уақыт' : date('m.Y', strtotime($exp['end_date'])) ?>
                                </p>
                                <?php if ($exp['description']): ?>
                                    <p><?= nl2br(htmlspecialchars($exp['description'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Образование -->
                    <?php if (!empty($education)): ?>
                    <div class="profile-card">
                        <h4><i class="fas fa-graduation-cap me-2"></i>Білім</h4>
                        <?php foreach ($education as $edu): ?>
                            <div class="timeline-item">
                                <h5><?= htmlspecialchars($edu['institution']) ?></h5>
                                <p class="text-muted mb-1">
                                    <?= htmlspecialchars($edu['degree']) ?> 
                                    <?php if ($edu['field_of_study']): ?>
                                        - <?= htmlspecialchars($edu['field_of_study']) ?>
                                    <?php endif; ?>
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-calendar me-2"></i>
                                    <?= date('Y', strtotime($edu['start_date'])) ?> - 
                                    <?= date('Y', strtotime($edu['end_date'])) ?>
                                </p>
                                <?php if ($edu['description']): ?>
                                    <p><?= nl2br(htmlspecialchars($edu['description'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Для HR менеджеров -->
                    <div class="profile-card">
                        <h4><i class="fas fa-building me-2"></i>Компания туралы</h4>
                        <?php if ($user['bio']): ?>
                            <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted">Компания туралы ақпарат жоқ</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
