<?php
// Тестовый файл для проверки добавления языка
session_start();

// Устанавливаем тестового пользователя (замените на ваш ID)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 10; // Ваш user_id
    echo "⚠️ Установлен тестовый user_id = 10<br><br>";
}

require_once 'config/database.php';

echo "<h2>Тест добавления языка</h2>";

// Тестовые данные
$test_language = 'English';
$test_proficiency = 'B2';

echo "<strong>Тестовые данные:</strong><br>";
echo "Language: $test_language<br>";
echo "Proficiency: $test_proficiency<br>";
echo "User ID: " . $_SESSION['user_id'] . "<br><br>";

try {
    // Удаляем если уже есть
    $delete = $pdo->prepare("DELETE FROM user_languages WHERE user_id = ? AND language = ?");
    $delete->execute([$_SESSION['user_id'], $test_language]);
    echo "✓ Удалены старые записи<br>";
    
    // Добавляем новую запись
    $stmt = $pdo->prepare("
        INSERT INTO user_languages (user_id, language, proficiency)
        VALUES (?, ?, ?)
    ");
    
    $result = $stmt->execute([$_SESSION['user_id'], $test_language, $test_proficiency]);
    
    if ($result) {
        echo "✓ <strong style='color: green;'>Язык успешно добавлен!</strong><br><br>";
        
        // Проверяем что сохранилось
        $check = $pdo->prepare("SELECT * FROM user_languages WHERE user_id = ? AND language = ?");
        $check->execute([$_SESSION['user_id'], $test_language]);
        $saved = $check->fetch();
        
        echo "<strong>Сохраненные данные:</strong><br>";
        echo "<pre>";
        print_r($saved);
        echo "</pre>";
        
        if ($saved['proficiency'] === $test_proficiency) {
            echo "✓ <strong style='color: green;'>Уровень сохранен правильно!</strong><br>";
        } else {
            echo "✗ <strong style='color: red;'>Уровень сохранен неправильно!</strong><br>";
            echo "Ожидалось: $test_proficiency<br>";
            echo "Получено: " . $saved['proficiency'] . "<br>";
        }
    } else {
        echo "✗ <strong style='color: red;'>Ошибка при добавлении</strong><br>";
    }
    
} catch (Exception $e) {
    echo "✗ <strong style='color: red;'>Ошибка: " . $e->getMessage() . "</strong><br>";
}

echo "<br><hr><br>";
echo "<h3>Все языки пользователя:</h3>";

$all = $pdo->prepare("SELECT * FROM user_languages WHERE user_id = ?");
$all->execute([$_SESSION['user_id']]);
$languages = $all->fetchAll();

if (empty($languages)) {
    echo "<p>Нет языков</p>";
} else {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Language</th><th>Proficiency</th><th>Created</th></tr>";
    foreach ($languages as $lang) {
        echo "<tr>";
        echo "<td>" . $lang['id'] . "</td>";
        echo "<td>" . htmlspecialchars($lang['language']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($lang['proficiency']) . "</strong></td>";
        echo "<td>" . $lang['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<br><br>";
echo "<a href='profile_jobseeker.php'>← Вернуться к профилю</a>";
?>
