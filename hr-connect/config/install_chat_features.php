<?php
/**
 * Скрипт для установки дополнительных функций чата
 * Запустите этот файл один раз через браузер: http://localhost/hr-connect/config/install_chat_features.php
 */

require_once 'database.php';

echo "<h2>Установка дополнительных функций чата</h2>";
echo "<pre>";

try {
    // 1. Добавляем поля в таблицу messages
    echo "1. Добавление полей в таблицу messages...\n";
    try {
        $pdo->exec("ALTER TABLE messages ADD COLUMN edited_at TIMESTAMP NULL DEFAULT NULL");
        echo "   ✓ Поле edited_at добавлено\n";
    } catch (PDOException $e) {
        echo "   - Поле edited_at уже существует\n";
    }
    
    try {
        $pdo->exec("ALTER TABLE messages ADD COLUMN is_deleted TINYINT(1) DEFAULT 0");
        echo "   ✓ Поле is_deleted добавлено\n";
    } catch (PDOException $e) {
        echo "   - Поле is_deleted уже существует\n";
    }
    
    // 2. Создаем таблицу message_reactions
    echo "\n2. Создание таблицы message_reactions...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS message_reactions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            message_id INT NOT NULL,
            user_id INT NOT NULL,
            reaction VARCHAR(10) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_reaction (message_id, user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✓ Таблица message_reactions создана\n";
    
    // 3. Создаем таблицу typing_status
    echo "\n3. Создание таблицы typing_status...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS typing_status (
            id INT PRIMARY KEY AUTO_INCREMENT,
            conversation_id INT NOT NULL,
            user_id INT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_typing (conversation_id, user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✓ Таблица typing_status создана\n";
    
    // 4. Создаем таблицу user_online_status
    echo "\n4. Создание таблицы user_online_status...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_online_status (
            user_id INT PRIMARY KEY,
            last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_online TINYINT(1) DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✓ Таблица user_online_status создана\n";
    
    // 5. Создаем индексы
    echo "\n5. Создание индексов...\n";
    try {
        $pdo->exec("CREATE INDEX idx_typing_updated ON typing_status(updated_at)");
        echo "   ✓ Индекс idx_typing_updated создан\n";
    } catch (PDOException $e) {
        echo "   - Индекс idx_typing_updated уже существует\n";
    }
    
    try {
        $pdo->exec("CREATE INDEX idx_online_status ON user_online_status(is_online, last_seen)");
        echo "   ✓ Индекс idx_online_status создан\n";
    } catch (PDOException $e) {
        echo "   - Индекс idx_online_status уже существует\n";
    }
    
    echo "\n✅ Установка завершена успешно!\n";
    echo "\nТеперь вы можете использовать все функции чата:\n";
    echo "- Реакции на сообщения 👍 ❤️ 😂\n";
    echo "- Редактирование сообщений ✏️\n";
    echo "- Удаление сообщений 🗑️\n";
    echo "- Статус онлайн/оффлайн 🟢\n";
    echo "- Индикатор 'печатает...' ⌨️\n";
    
    echo "\n⚠️ ВАЖНО: Удалите этот файл после установки для безопасности!\n";
    
} catch (PDOException $e) {
    echo "\n❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
