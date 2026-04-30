<?php
// Скрипт для создания админского аккаунта

require_once 'database.php';

$email = 'admin@hrconnect.kz';
$password = '123456A!';
$full_name = 'Администратор';

// Хешируем пароль
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Изменяем тип user_type
    $pdo->exec("ALTER TABLE users MODIFY COLUMN user_type ENUM('job_seeker', 'hr', 'admin') NOT NULL");
    echo "✅ Тип user_type обновлен\n";
    
    // Проверяем существует ли админ
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->fetch()) {
        // Обновляем существующего
        $stmt = $pdo->prepare("UPDATE users SET password = ?, user_type = 'admin', full_name = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $full_name, $email]);
        echo "✅ Админ обновлен\n";
    } else {
        // Создаем нового
        $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, user_type) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$email, $hashed_password, $full_name]);
        echo "✅ Админ создан\n";
    }
    
    echo "\n📋 Данные для входа:\n";
    echo "Email: admin@hrconnect.kz\n";
    echo "Пароль: 123456A!\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}
