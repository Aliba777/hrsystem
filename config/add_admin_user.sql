-- Добавление типа пользователя admin и создание админского аккаунта

USE hr_connect;

-- Изменяем тип user_type чтобы добавить admin
ALTER TABLE users 
MODIFY COLUMN user_type ENUM('job_seeker', 'hr', 'admin') NOT NULL;

-- Создаем админа (логин: admin, пароль: 123456A!)
INSERT INTO users (username, email, password, full_name, user_type, created_at) 
VALUES (
    'admin',
    'admin@hrconnect.kz',
    '$2y$10$YourHashedPasswordHere',
    'Администратор',
    'admin',
    NOW()
) ON DUPLICATE KEY UPDATE user_type = 'admin';

-- Проверяем
SELECT id, username, email, full_name, user_type FROM users WHERE user_type = 'admin';
