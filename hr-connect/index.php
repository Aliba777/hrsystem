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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --success: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Навигация */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 30px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-link {
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary) !important;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--gradient);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary);
            border-radius: 50px;
            padding: 10px 28px;
            font-weight: 600;
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        /* Герой секция */
        .hero-section {
            background: var(--gradient);
            color: "purple";
            padding: 120px 0 100px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"><path d="M0,0 L1000,100 L0,100 Z" fill="%23f5f7fa"/></svg>') bottom/100% auto no-repeat;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* Преимущества */
        .features-section {
            padding: 100px 0;
            background: #f5f7fa;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .feature-card {
            background: white;
            border: none;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: var(--gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .feature-text {
            color: #666;
            line-height: 1.6;
        }

        /* Как это работает */
        .how-it-works {
            padding: 100px 0;
            background: white;
        }

        .step-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            position: relative;
            border: 1px solid #e9ecef;
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: var(--gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            margin: 0 auto 1rem;
        }

        /* CTA секция */
        .cta-section {
            background: var(--gradient-secondary);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-light {
            background: white;
            color: var(--primary);
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-light:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.3);
        }

        /* Футер */
        .footer {
            background: var(--dark);
            color: white;
            padding: 50px 0 20px;
        }

        /* Анимации */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .floating {
            animation: float 6s ease-in-out infinite;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .feature-card {
                margin-bottom: 2rem;
            }
        }
    </style>
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
    <section class="hero-section">
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
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 400'%3E%3Cpath fill='%23ffffff' opacity='0.2' d='M300,200 Q400,100 500,200 T700,200'/%3E%3Ccircle fill='%23ffffff' opacity='0.3' cx='300' cy='200' r='80'/%3E%3Ccircle fill='%23ffffff' opacity='0.5' cx='300' cy='200' r='50'/%3E%3C/svg%3E" 
                             alt="HR Connect" class="img-fluid" style="max-width: 80%;">
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
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <h2 class="section-title">Қалай жұмыс істей