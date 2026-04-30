<?php
session_start();

// Только HR может просматривать детали резюме
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'hr') {
    header("Location: login.php");
    exit;
}

// Предотвращаем кеширование страницы
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once 'config/database.php';

$resume_id = $_GET['id'] ?? 0;

// Получаем резюме с полной информацией о соискателе
$stmt = $pdo->prepare("
    SELECT r.*, u.full_name, u.email, u.phone, u.city, u.bio, u.linkedin, u.telegram
    FROM resumes r
    JOIN users u ON r.job_seeker_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$resume_id]);
$resume = $stmt->fetch();

if (!$resume) {
    header("Location: resumes.php");
    exit;
}

// Получаем образование
$education = $pdo->prepare("SELECT * FROM user_education WHERE user_id = ? ORDER BY start_date DESC");
$education->execute([$resume['job_seeker_id']]);
$educations = $education->fetchAll();

// Получаем опыт
$experience = $pdo->prepare("SELECT * FROM user_experience WHERE user_id = ? ORDER BY start_date DESC");
$experience->execute([$resume['job_seeker_id']]);
$experiences = $experience->fetchAll();

// Получаем навыки
$skills = $pdo->prepare("SELECT * FROM user_skills WHERE user_id = ?");
$skills->execute([$resume['job_seeker_id']]);
$user_skills = $skills->fetchAll();

// Получаем языки
$languages = $pdo->prepare("SELECT * FROM user_languages WHERE user_id = ?");
$languages->execute([$resume['job_seeker_id']]);
$user_languages = $languages->fetchAll();

// Проверяем существующий оффер от этого HR (только активные)
$existing_offer = null;
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'hr') {
    $check_offer = $pdo->prepare("SELECT * FROM offers WHERE resume_id = ? AND hr_id = ? LIMIT 1");
    $check_offer->execute([$resume_id, $_SESSION['user_id']]);
    $existing_offer = $check_offer->fetch();
    
    // Если оффер не найден, убеждаемся что переменная null
    if (!$existing_offer) {
        $existing_offer = null;
    }
}

// Обработка отправки/обновления оффера
if ($_POST && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'hr') {
    $message = $_POST['message'];
    $salary_offer = $_POST['salary_offer'] ?? null;
    
    if ($existing_offer) {
        // Обновляем существующий оффер
        $stmt = $pdo->prepare("UPDATE offers SET message = ?, salary_offer = ? WHERE id = ?");
        $stmt->execute([$message, $salary_offer, $existing_offer['id']]);
        $success = "Оффер сәтті жаңартылды!";
        
        // Обновляем данные
        $check_offer->execute([$resume_id, $_SESSION['user_id']]);
        $existing_offer = $check_offer->fetch();
    } else {
        // Создаем новый оффер
        $stmt = $pdo->prepare("INSERT INTO offers (resume_id, hr_id, job_seeker_id, message, salary_offer) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$resume_id, $_SESSION['user_id'], $resume['job_seeker_id'], $message, $salary_offer]);
        $success = "Оффер сәтті жіберілді!";
        
        // Получаем созданный оффер
        $check_offer->execute([$resume_id, $_SESSION['user_id']]);
        $existing_offer = $check_offer->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($resume['title']) ?> - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-8">
                <!-- Основная информация о резюме -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h2><?= htmlspecialchars($resume['title']) ?></h2>
                        <h4 class="text-primary"><?= htmlspecialchars($resume['desired_position']) ?></h4>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <p><i class="fas fa-money-bill-wave me-2"></i><strong>Қалаған жалақы:</strong> <?= number_format($resume['desired_salary']) ?> ₸</p>
                                <p><i class="fas fa-clock me-2"></i><strong>Тәжірибе:</strong> <?= $resume['work_experience_years'] ?> жыл</p>
                            </div>
                            <div class="col-md-6">
                                <p><i class="fas fa-calendar me-2"></i><strong>Жарияланған:</strong> <?= date('d.m.Y', strtotime($resume['created_at'])) ?></p>
                            </div>
                        </div>

                        <hr>

                        <h5><i class="fas fa-info-circle me-2"></i>Сипаттама</h5>
                        <p><?= nl2br(htmlspecialchars($resume['description'])) ?></p>
                    </div>
                </div>

                <!-- Образование -->
                <?php if (!empty($educations)): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5><i class="fas fa-graduation-cap me-2"></i>Білім</h5>
                            <?php foreach ($educations as $edu): ?>
                                <div class="mb-3">
                                    <h6><?= htmlspecialchars($edu['institution']) ?></h6>
                                    <p class="mb-1"><?= htmlspecialchars($edu['degree']) ?> - <?= htmlspecialchars($edu['field_of_study']) ?></p>
                                    <small class="text-muted">
                                        <?= date('Y', strtotime($edu['start_date'])) ?> - 
                                        <?= $edu['end_date'] ? date('Y', strtotime($edu['end_date'])) : 'Қазір' ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Опыт работы -->
                <?php if (!empty($experiences)): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5><i class="fas fa-briefcase me-2"></i>Жұмыс тәжірибесі</h5>
                            <?php foreach ($experiences as $exp): ?>
                                <div class="mb-3">
                                    <h6><?= htmlspecialchars($exp['position']) ?></h6>
                                    <p class="mb-1"><?= htmlspecialchars($exp['company']) ?></p>
                                    <small class="text-muted">
                                        <?= date('m.Y', strtotime($exp['start_date'])) ?> - 
                                        <?= $exp['end_date'] ? date('m.Y', strtotime($exp['end_date'])) : 'Қазір' ?>
                                    </small>
                                    <?php if ($exp['description']): ?>
                                        <p class="mt-2"><?= nl2br(htmlspecialchars($exp['description'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Навыки и языки -->
                <div class="row">
                    <?php if (!empty($user_skills)): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5><i class="fas fa-star me-2"></i>Дағдылар</h5>
                                    <?php foreach ($user_skills as $skill): ?>
                                        <span class="skill-badge"><?= htmlspecialchars($skill['skill_name']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($user_languages)): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5><i class="fas fa-language me-2"></i>Тілдер</h5>
                                    <?php foreach ($user_languages as $lang): ?>
                                        <span class="language-badge"><?= htmlspecialchars($lang['language']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Боковая панель с контактами и формой оффера -->
            <div class="col-md-4">
                <!-- Контакты -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5><i class="fas fa-user me-2"></i>Байланыс</h5>
                        <p class="mb-2"><strong><?= htmlspecialchars($resume['full_name']) ?></strong></p>
                        <p class="mb-2"><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($resume['email']) ?></p>
                        <?php if ($resume['phone']): ?>
                            <p class="mb-2"><i class="fas fa-phone me-2"></i><?= htmlspecialchars($resume['phone']) ?></p>
                        <?php endif; ?>
                        <?php if ($resume['city']): ?>
                            <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($resume['city']) ?></p>
                        <?php endif; ?>
                        <?php if ($resume['linkedin']): ?>
                            <p class="mb-2"><i class="fab fa-linkedin me-2"></i><a href="<?= htmlspecialchars($resume['linkedin']) ?>" target="_blank">LinkedIn</a></p>
                        <?php endif; ?>
                        <?php if ($resume['telegram']): ?>
                            <p class="mb-2"><i class="fab fa-telegram me-2"></i><?= htmlspecialchars($resume['telegram']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Форма оффера для HR -->
                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'hr'): ?>
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-paper-plane me-2"></i>
                                <?= $existing_offer ? 'Офферді өңдеу' : 'Оффер жіберу' ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($success)): ?>
                                <div class="alert alert-primary">
                                    <i class="fas fa-check-circle me-2"></i><?= $success ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($existing_offer): ?>
                                <div class="alert alert-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Сіз бұрын оффер жібердіңіз. Статус: 
                                    <strong>
                                        <?= $existing_offer['status'] == 'pending' ? 'Күту' : 
                                           ($existing_offer['status'] == 'accepted' ? 'Қабылданды' : 'Қабылданбады') ?>
                                    </strong>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Ұсынылатын жалақы (₸)</label>
                                    <input type="number" name="salary_offer" class="form-control" 
                                           placeholder="350000" min="0"
                                           value="<?= $existing_offer ? $existing_offer['salary_offer'] : '' ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Хабарлама</label>
                                    <textarea name="message" class="form-control" rows="5" 
                                              placeholder="Сәлеметсіз бе! Біз сізге жұмыс ұсынғымыз келеді..." 
                                              required><?= $existing_offer ? htmlspecialchars($existing_offer['message']) : '' ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-<?= $existing_offer ? 'save' : 'paper-plane' ?> me-2"></i>
                                    <?= $existing_offer ? 'Офферді жаңарту' : 'Оффер жіберу' ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <div class="alert alert-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        Оффер жіберу үшін <a href="login.php">кіріңіз</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
