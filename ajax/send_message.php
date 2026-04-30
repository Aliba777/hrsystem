<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Рұқсат жоқ']);
    exit;
}

require_once '../config/database.php';

$conversation_id = $_POST['conversation_id'] ?? 0;
$message = trim($_POST['message'] ?? '');
$user_id = $_SESSION['user_id'];

// Проверяем доступ к беседе
$stmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (hr_id = ? OR jobseeker_id = ?)");
$stmt->execute([$conversation_id, $user_id, $user_id]);
$conversation = $stmt->fetch();

if (!$conversation) {
    echo json_encode(['success' => false, 'message' => 'Әңгіме табылмады']);
    exit;
}

if (empty($message) && empty($_FILES['file']['name'])) {
    echo json_encode(['success' => false, 'message' => 'Хабарлама бос болмауы керек']);
    exit;
}

// Обработка файла
$file_path = null;
$file_name = null;

if (!empty($_FILES['file']['name'])) {
    $upload_dir = '../uploads/chat/';
    
    // Создаем директорию если не существует
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['success' => false, 'message' => 'Файл форматы қолдау көрсетілмейді']);
        exit;
    }
    
    // Проверка размера (макс 5MB)
    if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Файл өте үлкен (макс 5MB)']);
        exit;
    }
    
    $file_name = $_FILES['file']['name'];
    $unique_name = uniqid() . '_' . time() . '.' . $file_extension;
    $file_path = 'uploads/chat/' . $unique_name;
    
    if (!move_uploaded_file($_FILES['file']['tmp_name'], '../' . $file_path)) {
        echo json_encode(['success' => false, 'message' => 'Файлды жүктеу кезінде қате']);
        exit;
    }
}

// Если сообщение пустое, но есть файл
if (empty($message)) {
    $message = '[Файл жіберілді]';
}

// Сохраняем сообщение
try {
    $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, message, file_path, file_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$conversation_id, $user_id, $message, $file_path, $file_name]);
    
    // Обновляем время последнего сообщения
    $stmt = $pdo->prepare("UPDATE conversations SET last_message_at = NOW() WHERE id = ?");
    $stmt->execute([$conversation_id]);
    
    echo json_encode(['success' => true, 'message' => 'Хабарлама жіберілді']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Қате орын алды: ' . $e->getMessage()]);
}
