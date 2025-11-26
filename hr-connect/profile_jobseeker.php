<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'job_seeker') {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Получаем образование
$edu_stmt = $pdo->prepare("SELECT * FROM user_education WHERE user_id = ? ORDER BY end_date DESC");
$edu_stmt->execute([$_SESSION['user_id']]);
$education = $edu_stmt->fetchAll();

// Получаем языки
$lang_stmt = $pdo->prepare("SELECT * FROM user_languages WHERE user_id = ?");
$lang_stmt->execute([$_SESSION['user_id']]);
$languages = $lang_stmt->fetchAll();

// Получаем другие контакты
$contacts_stmt = $pdo->prepare("SELECT * FROM user_other_contacts WHERE user_id = ?");
$contacts_stmt->execute([$_SESSION['user_id']]);
$other_contacts = $contacts_stmt->fetchAll();

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><?= htmlspecialchars($user['first_name'] ?? 'Аты') ?> <?= htmlspecialchars($user['last_name'] ?? 'Тегі') ?></h2>
                    <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($user['city'] ?? 'Қала') ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="fas fa-cog me-2"></i>Баптаулар
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <!-- Левая колонка -->
            <div class="col-md-4">
                <!-- Личная информация -->
                <div class="profile-section">
                    <div class="section-title">
                        <span><i class="fas fa-user me-2"></i>Жеке ақпарат</span>
                        <i class="fas fa-edit add-btn" onclick="editPersonalInfo()"></i>
                    </div>
                    <div class="info-badge">
                        <strong>Аты-жөні:</strong><br>
                        <?= htmlspecialchars($user['first_name'] ?? '') ?> <?= htmlspecialchars($user['last_name'] ?? '') ?>
                    </div>
                    <div class="info-badge">
                        <strong>Қала:</strong><br>
                        <?= htmlspecialchars($user['city'] ?? 'Көрсетілмеген') ?>
                    </div>
                    <div class="info-badge">
                        <strong>Туған күні:</strong><br>
                        <?= $user['birth_date'] ? date('d.m.Y', strtotime($user['birth_date'])) : 'Көрсетілмеген' ?>
                    </div>
                    <div class="info-badge">
                        <strong>Жынысы:</strong><br>
                        <?php
                        $genders = ['male' => 'Ер', 'female' => 'Әйел', 'other' => 'Басқа'];
                        echo $genders[$user['gender']] ?? 'Көрсетілмеген';
                        ?>
                    </div>
                    <div class="info-badge">
                        <strong>Азаматтығы:</strong><br>
                        <?= htmlspecialchars($user['citizenship'] ?? 'Көрсетілмеген') ?>
                    </div>
                </div>

                <!-- Контакты -->
                <div class="profile-section">
                    <div class="section-title">
                        <span><i class="fas fa-address-book me-2"></i>Байланыс</span>
                    </div>
                    
                    <div class="contact-btn" onclick="openContactModal('telegram')">
                        <div class="d-flex align-items-center">
                            <i class="fab fa-telegram"></i>
                            <span>Telegram</span>
                        </div>
                        <span class="text-muted"><?= htmlspecialchars($user['telegram'] ?? 'Қосу') ?></span>
                    </div>
                    
                    <div class="contact-btn" onclick="openContactModal('whatsapp')">
                        <div class="d-flex align-items-center">
                            <i class="fab fa-whatsapp"></i>
                            <span>WhatsApp</span>
                        </div>
                        <span class="text-muted"><?= htmlspecialchars($user['whatsapp'] ?? 'Қосу') ?></span>
                    </div>
                    
                    <div class="contact-btn" onclick="openContactModal('instagram')">
                        <div class="d-flex align-items-center">
                            <i class="fab fa-instagram"></i>
                            <span>Instagram</span>
                        </div>
                        <span class="text-muted"><?= htmlspecialchars($user['instagram'] ?? 'Қосу') ?></span>
                    </div>
                    
                    <div class="contact-btn" onclick="openContactModal('email')">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-envelope"></i>
                            <span>Почта</span>
                        </div>
                        <span class="text-muted"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    
                    <div class="contact-btn" onclick="openContactModal('phone')">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-phone"></i>
                            <span>Телефон</span>
                        </div>
                        <span class="text-muted"><?= htmlspecialchars($user['phone'] ?? 'Қосу') ?></span>
                    </div>
                </div>

                <!-- Другие способы связи -->
                <div class="profile-section">
                    <div class="section-title">
                        <span><i class="fas fa-share-alt me-2"></i>Әлеуметтік желілер</span>
                        <span class="add-btn" onclick="addOtherContact()">+ Қосу</span>
                    </div>
                    
                    <?php if (empty($other_contacts)): ?>
                        <p class="text-muted">Әлеуметтік желілер қосылмаған</p>
                    <?php else: ?>
                        <div id="otherContactsList">
                            <?php foreach ($other_contacts as $contact): ?>
                                <div class="contact-item-wrapper" style="position: relative; margin-bottom: 10px;">
                                    <div class="contact-btn" onclick="window.open('<?= htmlspecialchars($contact['contact_url']) ?>', '_blank')" style="cursor: pointer;">
                                        <div class="d-flex align-items-center">
                                            <?php
                                            // Иконки для разных типов
                                            $icons = [
                                                'github' => 'fab fa-github',
                                                'behance' => 'fab fa-behance',
                                                'dribbble' => 'fab fa-dribbble',
                                                'vk' => 'fab fa-vk',
                                                'linkedin' => 'fab fa-linkedin',
                                                'facebook' => 'fab fa-facebook',
                                                'twitter' => 'fab fa-twitter',
                                                'youtube' => 'fab fa-youtube',
                                                'website' => 'fas fa-link',
                                                'custom' => 'fas fa-link'
                                            ];
                                            $icon = $icons[$contact['contact_type']] ?? 'fas fa-link';
                                            ?>
                                            <i class="<?= $icon ?>" style="font-size: 1.5rem; color: #667eea; margin-right: 15px;"></i>
                                            <div class="d-flex flex-column flex-grow-1">
                                                <span><?= htmlspecialchars($contact['contact_description']) ?></span>
                                                <small class="text-muted" style="word-break: break-all;"><?= htmlspecialchars($contact['contact_url']) ?></small>
                                            </div>
                                        </div>
                                        <i class="fas fa-external-link-alt"></i>
                                    </div>
                                    <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteOtherContact(<?= $contact['id'] ?>)" style="position: absolute; top: 10px; right: 10px; z-index: 10; padding: 5px 10px;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Правая колонка -->
            <div class="col-md-8">
                <!-- Образование -->
                <div class="profile-section">
                    <div class="section-title">
                        <span><i class="fas fa-graduation-cap me-2"></i>Білім</span>
                        <span class="add-btn" onclick="addEducation()">+ Қосу</span>
                    </div>
                    
                    <?php if (empty($education)): ?>
                        <p class="text-muted">Білім туралы ақпарат қосылмаған</p>
                    <?php else: ?>
                        <?php foreach ($education as $edu): ?>
                            <div class="info-badge mb-3">
                                <h5><?= htmlspecialchars($edu['institution']) ?></h5>
                                <p class="mb-1"><strong><?= htmlspecialchars($edu['level'] ?? '') ?></strong></p>
                                <?php if ($edu['faculty']): ?>
                                    <p class="mb-1">Факультет: <?= htmlspecialchars($edu['faculty']) ?></p>
                                <?php endif; ?>
                                <?php if ($edu['field_of_study']): ?>
                                    <p class="mb-1">Мамандық: <?= htmlspecialchars($edu['field_of_study']) ?></p>
                                <?php endif; ?>
                                <p class="text-muted mb-0">
                                    <?= date('Y', strtotime($edu['start_date'])) ?> - 
                                    <?= date('Y', strtotime($edu['end_date'])) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Языки -->
                <div class="profile-section">
                    <div class="section-title">
                        <span><i class="fas fa-language me-2"></i>Тілдер</span>
                        <span class="add-btn" onclick="addLanguage()">+ Қосу</span>
                    </div>
                    
                    <?php if (empty($languages)): ?>
                        <p class="text-muted">Тілдер қосылмаған</p>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($languages as $lang): ?>
                                <span class="badge bg-primary" style="font-size: 1rem; padding: 10px 15px;">
                                    <?= htmlspecialchars($lang['language']) ?> - <?= htmlspecialchars($lang['proficiency']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Модальные окна будут добавлены в следующем файле -->
    <?php include 'includes/profile_modals.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="js/profile.js"></script>
    <script>
        // Инициализация Select2 для поиска языков
        $(document).ready(function() {
            $('#languageSelect').select2({
                placeholder: 'Таңдаңыз немесе іздеңіз...',
                allowClear: true,
                dropdownParent: $('#languageModal'),
                language: {
                    noResults: function() {
                        return "Тіл табылмады";
                    },
                    searching: function() {
                        return "Іздеу...";
                    }
                }
            });
        });
    </script>
</body>
</html>
