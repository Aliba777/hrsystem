<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Рұқсат жоқ']);
    exit;
}

require_once '../config/database.php';

$conversation_id = $_GET['conversation_id'] ?? 0;
$last_id = $_GET['last_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Проверяем доступ к беседе
$stmt = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (hr_id = ? OR jobseeker_id = ?)");
$stmt->execute([$conversation_id, $user_id, $user_id]);
$conversation = $stmt->fetch();

if (!$conversation) {
    echo json_encode(['success' => false, 'message' => 'Әңгіме табылмады']);
    exit;
}

// Получаем новые сообщения
$stmt = $pdo->prepare("
    SELECT m.*, u.full_name as sender_name 
    FROM messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.conversation_id = ? AND m.id > ?
    ORDER BY m.created_at ASC
");
$stmt->execute([$conversation_id, $last_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем реакции для новых сообщений
if (!empty($messages)) {
    $message_ids = array_column($messages, 'id');
    $placeholders = str_repeat('?,', count($message_ids) - 1) . '?';
    
    try {
        $stmt = $pdo->prepare("
            SELECT message_id, reaction, COUNT(*) as count 
            FROM message_reactions 
            WHERE message_id IN ($placeholders)
            GROUP BY message_id, reaction
        ");
        $stmt->execute($message_ids);
        $reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Группируем реакции по message_id
        $reactions_by_message = [];
        foreach ($reactions as $r) {
            $reactions_by_message[$r['message_id']][] = $r;
        }
        
        // Добавляем реакции к сообщениям
        foreach ($messages as &$message) {
            $message['reactions'] = $reactions_by_message[$message['id']] ?? [];
        }
    } catch (PDOException $e) {
        // Таблица реакций еще не создана
        foreach ($messages as &$message) {
            $message['reactions'] = [];
        }
    }
    
    // Отмечаем новые сообщения как прочитанные
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id != ? AND id > ?");
    $stmt->execute([$conversation_id, $user_id, $last_id]);
}

echo json_encode([
    'success' => true,
    'messages' => $messages
]);
