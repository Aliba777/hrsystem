<?php
session_start();
require_once 'config/database.php';

$error = '';
if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];

    // Валидация
    if ($password !== $confirm_password) {
        $error = "Пароли не совпадают!";
    } else {
        // Проверяем, нет ли уже пользователя с таким email
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->execute([$email]);
        
        if ($check_stmt->fetch()) {
            $error = "Пользователь с таким email уже существует!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, user_type, full_name, phone) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$email, $hashed_password, $user_type, $full_name, $phone])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_type'] = $user_type;
                $_SESSION['full_name'] = $full_name;
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Ошибка при регистрации!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - HR Connect</title>
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
            background: var(--gradient);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }

        .register-card {
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
            text-align: center;
            padding: 30px;
            border-bottom: none;
        }

        .card-header h2 {
            margin: 0;
            font-weight: 700;
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

        .input-group-text {
            background: var(--primary);
            border: none;
            color: white;
            border-radius: 12px 0 0 12px;
        }

        .btn-register {
            background: var(--gradient);
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
        }

        .user-type-cards {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .user-type-card {
            flex: 1;
            text-align: center;
            padding: 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-type-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
        }

        .user-type-card.selected {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
        }

        .user-type-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }

        .strength-weak { background: #dc3545; width: 25%; }
        .strength-medium { background: #ffc107; width: 50%; }
        .strength-strong { background: #28a745; width: 100%; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="register-card">
                        <div class="card-header">
                            <h2><i class="fas fa-user-plus me-2"></i>Присоединяйтесь к HR Connect</h2>
                            <p class="mb-0">Создайте аккаунт и начните поиск работы или сотрудников</p>
                        </div>
                        
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= $error ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" id="registerForm">
                                <!-- Выбор типа пользователя -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Я хочу:</label>
                                    <div class="user-type-cards">
                                        <div class="user-type-card" onclick="selectUserType('job_seeker', this)">
                                            <i class="fas fa-search"></i>
                                            <div>Найти работу</div>
                                            <small class="text-muted">Соискатель</small>
                                        </div>
                                        <div class="user-type-card" onclick="selectUserType('hr', this)">
                                            <i class="fas fa-briefcase"></i>
                                            <div>Найти сотрудников</div>
                                            <small class="text-muted">HR менеджер</small>
                                        </div>
                                    </div>
                                    <input type="hidden" name="user_type" id="userType" required>
                                </div>

                                <!-- Основная информация -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">ФИО *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" name="full_name" class="form-control" placeholder="Әлібек Әлібекұлы Әлібеков" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email" class="form-control" placeholder="example@mail.ru" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Телефон</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="tel" name="phone" class="form-control" placeholder="+7 (777) 999-99-99">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Пароль *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" name="password" id="password" class="form-control" placeholder="Минимум 6 символов" required minlength="6">
                                            </div>
                                            <div class="password-strength" id="passwordStrength"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Подтверждение пароля *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" name="confirm_password" class="form-control" placeholder="Повторите пароль" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            Я соглашаюсь с <a href="#">условиями использования</a> и <a href="#">политикой конфиденциальности</a>
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-register w-100 mb-3">
                                    <i class="fas fa-user-plus me-2"></i>Создать аккаунт
                                </button>

                                <div class="login-link">
                                    <p class="mb-0">Уже есть аккаунт? <a href="login.php" class="text-decoration-none">Войдите здесь</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectUserType(type, element) {
            document.getElementById('userType').value = type;
            
            // Убираем выделение со всех карточек
            document.querySelectorAll('.user-type-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Добавляем выделение выбранной карточке
            element.classList.add('selected');
        }

        // Проверка сложности пароля
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            
            strengthBar.className = 'password-strength ';
            if (password.length === 0) {
                strengthBar.style.width = '0%';
            } else if (strength < 2) {
                strengthBar.className += 'strength-weak';
            } else if (strength < 4) {
                strengthBar.className += 'strength-medium';
            } else {
                strengthBar.className += 'strength-strong';
            }
        });

        // Валидация формы
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const userType = document.getElementById('userType').value;
            if (!userType) {
                e.preventDefault();
                alert('Пожалуйста, выберите тип аккаунта');
                return false;
            }
        });
    </script>
</body>
</html>