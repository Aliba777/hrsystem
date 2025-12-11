<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

// Обновляем онлайн статус
$stmt = $pdo->prepare("
    INSERT INTO user_online_status (user_id, last_seen, is_online) 
    VALUES (?, NOW(), 1) 
    ON DUPLICATE KEY UPDATE last_seen = NOW(), is_online = 1
");
$stmt->execute([$user_id]);

echo json_encode(['success' => true]);
