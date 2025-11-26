-- Проверка и исправление таблицы user_languages

-- 1. Проверяем текущую структуру
DESCRIBE user_languages;

-- 2. Удаляем старые записи без уровня
DELETE FROM user_languages WHERE proficiency IS NULL OR proficiency = '';

-- 3. Пересоздаем таблицу с правильной структурой
DROP TABLE IF EXISTS user_languages;

CREATE TABLE user_languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    language VARCHAR(100) NOT NULL,
    proficiency ENUM('A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'Родной') NOT NULL DEFAULT 'B1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_language (user_id, language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Проверяем результат
SELECT * FROM user_languages;
DESCRIBE user_languages;
