-- Расширенная структура профиля для соискателей

-- Обновление таблицы users
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS last_name VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS citizenship VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS instagram VARCHAR(100) DEFAULT NULL;

-- Таблица для других способов связи
CREATE TABLE IF NOT EXISTS user_other_contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    contact_type VARCHAR(50) NOT NULL COMMENT 'github, behance, dribbble, vk, website',
    contact_url VARCHAR(500) NOT NULL,
    contact_description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблица для изображений (резюме и портфолио)
CREATE TABLE IF NOT EXISTS user_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    image_type ENUM('resume', 'portfolio') NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Обновление таблицы образования
ALTER TABLE user_education 
ADD COLUMN IF NOT EXISTS level VARCHAR(50) DEFAULT NULL COMMENT 'Орта білім, Колледж, Бакалавр, Магистр, Доктор',
ADD COLUMN IF NOT EXISTS faculty VARCHAR(255) DEFAULT NULL;

-- Обновление таблицы языков
ALTER TABLE user_languages 
MODIFY proficiency ENUM('A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'Родной') DEFAULT 'B1';
