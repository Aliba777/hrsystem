<?php
require_once 'database.php';

try {
    // Читаем SQL файл
    $sql = file_get_contents(__DIR__ . '/update_chat_tables.sql');
    
    // Разделяем на отдельные запросы
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (empty($query) || strpos($query, '--') === 0) continue;
        
        try {
            $pdo->exec($query);
            echo "✓ Выполнен запрос: " . substr($query, 0, 50) . "...\n";
        } catch (PDOException $e) {
            // Игнорируем ошибки "уже существует"
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate column') === false) {
                echo "✗ Ошибка: " . $e->getMessage() . "\n";
            } else {
                echo "- Пропущен (уже существует): " . substr($query, 0, 50) . "...\n";
            }
        }
    }
    
    echo "\nОбновления применены успешно!\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
?>