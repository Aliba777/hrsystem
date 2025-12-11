<?php
session_start();

// Простая проверка функций чата
require_once 'config/database.php';

echo "<h2>Тест функций чата</h2>";

// Проверяем таблицы
$tables = ['message_reactions', 'typing_status', 'user_online_status'];

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "✓ Таблица $table: $count записей<br>";
    } catch (PDOException $e) {
        echo "✗ Ошибка с таблицей $table: " . $e->getMessage() . "<br>";
    }
}

// Проверяем поля в таблице messages
try {
    $stmt = $pdo->query("DESCRIBE messages");
    $fields = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<br><strong>Поля в таблице messages:</strong><br>";
    foreach ($fields as $field) {
        echo "- $field<br>";
    }
    
    if (in_array('edited_at', $fields) && in_array('is_deleted', $fields)) {
        echo "✓ Поля для редактирования добавлены<br>";
    } else {
        echo "✗ Поля для редактирования отсутствуют<br>";
    }
} catch (PDOException $e) {
    echo "✗ Ошибка: " . $e->getMessage() . "<br>";
}

echo "<br><a href='chat.php?id=1'>Перейти к тестовому чату</a>";
?>