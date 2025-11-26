-- Исправление кодировки для таблицы языков

-- Удаляем таблицу
DROP TABLE IF EXISTS user_languages;

-- Создаем заново с правильной кодировкой
CREATE TABLE user_languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    language VARCHAR(100) NOT NULL,
    proficiency VARCHAR(20) NOT NULL DEFAULT 'B1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_language (user_id, language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Проверяем
DESCRIBE user_languages;
