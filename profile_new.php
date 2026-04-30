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
$edu_stmt = $pdo->prepare("SELECT * FROM user_education WHERE user_id = ? ORDER BY start_date DESC");
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
        .profile-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .contact-btn {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            margin: 10px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .contact-btn:hover {
            border-color: #667eea;
            transform: translateX(5px);
        }
        .contact-btn i {
            font-size: 1.5rem;
            color: #667eea;
            margin-right: 15px;
        }
        .add-btn {
            color: #667eea;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .add-btn:hover {
            color: #764ba2;
        }
        .info-badge {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 5px 0;
        }
        .modal-content {
            border-radius: 20px;
            border: none;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
        }
        .social-icon-selector {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 20px 0;
        }
        .social-icon-selector i {
            font-size: 2rem;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .social-icon-selector i:hover,
        .social-icon-selector i.active {
            border-color: #667eea;
            color: #667eea;
            transform: scale(1.1);
        }
        .language-level-btn {
            padding: 8px 15px;
            border: 2px solid #e9ecef;
            border-radius: 20px;
            margin: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .language-level-btn:hover,
        .language-level-btn.active {
            border-color: #667eea;
            background: #667eea;
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
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Шығу</a>
            </div>
        </div>
    </nav>

    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><?= htmlspecialchars($user['first_name'] ?? '') ?> <?= htmlspecialchars($user['last_name'] ?? '') ?></h2>
                    <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($user['city'] ?? 'Қала көрсетілмеген') ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="fas fa-cog me-2"></i>Баптаулар
                    </button>
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
                        <div>
                            <i class="fab fa-telegram"></i>
                            <span>Telegram</span>
                        </div>
                        <span><?= htmlspecialchars($user['telegram'] ?? 'Қосу') ?></span>
                    </div>
                    
                    <div class="contact-btn" onclick="openContactModal('whatsapp')">
                        <div>
                            <i class="fab fa-whatsapp"></i>
                            <span>WhatsApp</span>
                        </div>
                        <span><?= htmlspecialchars($user['whatsapp'] ?? 'Қосу') ?></span>
                    </div>
                    
                    <div class="contact-btn" onclick="openContactModal('instagram')">
                        <div>
                            <i class="fab fa-instagram"></i>
                            <span>Instagram</span>
                        </div>
                        <span><?= htmlspecialchars($user['instagram'] ?? 'Қосу') ?></span>
                    </div>
                    
                    <div class="contact-btn" onclick="openContactModal('email')">
                        <div>
                            <i class="fas fa-envelope"></i>
                            <span>Email</span>
                        </div>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    
                    <div class="contact-btn" onclick="openContactModal('phone')">
                        <div>
                            <i class="fas fa-phone"></i>
                            <span>Телефон</span>
                        </div>
                        <span><?= htmlspecialchars($user['phone'] ?? 'Қосу') ?></span>
                    </div>
                </div>

                <!-- Другие способы связи -->
                <div class="profile-section">
                    <div class="section-title">
                        <span>Басқа байланыс әдістері</span>
                        <span class="add-btn" onclick="addOtherContact()">+ Қосу</span>
                    </div>
                    
                    <div id="otherContactsList">
                        <?php foreach ($other_contacts as $contact): ?>
                            <div class="contact-btn">
                                <div>
                                    <i class="fas fa-link"></i>
                                    <span><?= htmlspecialchars($contact['contact_description']) ?></span>
                                </div>
                                <span><?= htmlspecialchars($contact['contact_value']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

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
                                <p class="mb-1">Факультет: <?= htmlspecialchars($edu['faculty'] ?? '') ?></p>
                                <p class="mb-1">Мамандық: <?= htmlspecialchars($edu['field_of_study'] ?? '') ?></p>
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

    <!-- Modal: Личная информация -->
    <div class="modal fade" id="personalInfoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Жеке ақпаратты өңдеу</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="personalInfoForm" action="ajax/update_personal_info.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Аты *</label>
                            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Тегі *</label>
                            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Қала *</label>
                            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Туған күні</label>
                            <input type="date" name="birth_date" class="form-control" value="<?= $user['birth_date'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Жынысы</label>
                            <select name="gender" class="form-control">
                                <option value="">Таңдаңыз</option>
                                <option value="male" <?= $user['gender'] == 'male' ? 'selected' : '' ?>>Ер</option>
                                <option value="female" <?= $user['gender'] == 'female' ? 'selected' : '' ?>>Әйел</option>
                                <option value="other" <?= $user['gender'] == 'other' ? 'selected' : '' ?>>Басқа</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Азаматтығы</label>
                            <input type="text" name="citizenship" class="form-control" value="<?= htmlspecialchars($user['citizenship'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Сақтау</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Контакт -->
    <div class="modal fade" id="contactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalTitle">Байланыс</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="contactForm" action="ajax/update_contact.php" method="POST">
                        <input type="hidden" name="contact_type" id="contactType">
                        <div class="mb-3">
                            <label class="form-label" id="contactLabel">Мән</label>
                            <input type="text" name="contact_value" id="contactValue" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Сақтау</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Другие контакты -->
    <div class="modal fade" id="otherContactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Байланыс әдісі</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="social-icon-selector">
                        <i class="fab fa-github" data-type="github"></i>
                        <i class="fab fa-behance" data-type="behance"></i>
                        <i class="fab fa-dribbble" data-type="dribbble"></i>
                        <i class="fab fa-vk" data-type="vk"></i>
                        <i class="fas fa-link" data-type="website"></i>
                    </div>
                    <p class="text-center text-muted">Әлеуметтік желі, блог немесе жеке сайт</p>
                    <form id="otherContactForm" action="ajax/add_other_contact.php" method="POST">
                        <input type="hidden" name="contact_type" id="otherContactType">
                        <div class="mb-3">
                            <label class="form-label">Сілтеме</label>
                            <input type="url" name="contact_value" class="form-control" placeholder="https://" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Сипаттама</label>
                            <input type="text" name="contact_description" class="form-control" placeholder='Мысалы, "Менің блогым про дизайн"'>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Сақтау</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Образование -->
    <div class="modal fade" id="educationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Білім қосу</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="educationForm" action="ajax/add_education.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Білім деңгейі *</label>
                            <select name="level" class="form-control" required>
                                <option value="">Таңдаңыз</option>
                                <option value="Среднее">Орта білім</option>
                                <option value="Колледж">Колледж</option>
                                <option value="Бакалавр">Бакалавр</option>
                                <option value="Магистр">Магистр</option>
                                <option value="Доктор">Доктор (PhD)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Оқу орны *</label>
                            <input type="text" name="institution" class="form-control" placeholder="Университет атауын енгізіңіз" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Факультет</label>
                            <input type="text" name="faculty" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Мамандық</label>
                            <input type="text" name="field_of_study" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Басталған жылы *</label>
                                <input type="number" name="start_year" class="form-control" min="1950" max="2030" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Аяқталған жылы *</label>
                                <input type="number" name="end_year" class="form-control" min="1950" max="2030" required>
                                <small class="text-muted">Егер әлі оқисаңыз, болжамды жылды көрсетіңіз</small>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Сақтау</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Язык -->
    <div class="modal fade" id="languageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Тіл қосу</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="languageForm" action="ajax/add_language.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Тіл *</label>
                            <select name="language" class="form-control" required>
                                <option value="">Таңдаңыз</option>
                                <option value="Қазақ">Қазақ</option>
                                <option value="Русский">Русский</option>
                                <option value="English">English</option>
                                <option value="Deutsch">Deutsch</option>
                                <option value="Français">Français</option>
                                <option value="中文">中文</option>
                                <option value="Español">Español</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Деңгей *</label>
                            <div class="d-flex flex-wrap">
                                <span class="language-level-btn" data-level="A1">A1</span>
                                <span class="language-level-btn" data-level="A2">A2</span>
                                <span class="language-level-btn" data-level="B1">B1</span>
                                <span class="language-level-btn" data-level="B2">B2</span>
                                <span class="language-level-btn" data-level="C1">C1</span>
                                <span class="language-level-btn" data-level="C2">C2</span>
                                <span class="language-level-btn" data-level="Родной">Ана тілі</span>
                            </div>
                            <input type="hidden" name="proficiency" id="languageLevel" required>
                        </div>
                        <button type