<?php
session_start();
// Егер пайдаланушы авторизацияланған болса, dashboard-ға бағыттау
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Connect - Жұмыс немесе қызметкерлерді табыңыз</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-handshake me-2"></i>HR Connect
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="#features">Мүмкіндіктер</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="#how-it-works">Қалай жұмыс істейді</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="login.php">Кіру</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary" href="register.php">Қазір бастау</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Герой секция -->
    <section class="hero-section" style="margin-top: 70px;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title">
                        Армандаған жұмысыңызды табыңыз<br>немесе тамаша қызметкерді табыңыз
                    </h1>
                    <p class="hero-subtitle">
                        HR менеджерлері және жұмыс іздеушілер үшін заманауи платформа. 
                        Жылдам, ыңғайлы, тиімді.
                    </p>
                    <div class="hero-buttons">
                        <a href="register.php?type=job_seeker" class="btn btn-light btn-lg">
                            <i class="fas fa-search me-2"></i>Мен жұмыс іздеймін
                        </a>
                        <a href="register.php?type=hr" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-briefcase me-2"></i>Мен HR менеджермін
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="floating">
                        <i class="fas fa-users fa-10x text-white" style="opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Преимущества -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title">Неге HR Connect таңдайды</h2>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3 class="feature-title">Лездік жауап</h3>
                        <p class="feature-text">
                            Бос орындарға лездік жауап алыңыз. 
                            HR менеджерлері мен жұмыс іздеушілер арасындағы тікелей байланыс.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">Деректер қауіпсіздігі</h3>
                        <p class="feature-text">
                            Сіздің деректеріңіз сенімді қорғалған. 
                            Заманауи шифрлау технологиялары және жеке ақпаратты қорғау.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="feature-title">Ақылды аналитика</h3>
                        <p class="feature-text">
                            Жауаптар статистикасын бақылаңыз, 
                            тиімділікті талдаңыз және сарапталған шешімдер қабылдаңыз.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Как это работает -->
    <section class="how-it-works" id="how-it-works" style="background: white; padding: 80px 0;">
        <div class="container">
            <h2 class="section-title">Қалай жұмыс істейді</h2>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h4>Тіркелу</h4>
                        <p>Жұмыс іздеуші немесе HR менеджер ретінде тіркеліңіз</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4>Іздеу немесе жариялау</h4>
                        <p>Вакансияларды іздеңіз немесе өз вакансияңызды жариялаңыз</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4>Байланысу</h4>
                        <p>Өтінім жіберіңіз немесе үміткерлермен байланысыңыз</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA секция -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Бүгін бастаңыз!</h2>
            <p class="lead">Мыңдаған компаниялар мен жұмыс іздеушілер бізге сенеді</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-light btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Тіркелу
                </a>
                <a href="login.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Кіру
                </a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
