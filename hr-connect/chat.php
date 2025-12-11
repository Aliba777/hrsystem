<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

$conversation_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Проверяем доступ к беседе
if ($user_type == 'hr') {
    $stmt = $pdo->prepare("SELECT c.*, u.full_name as contact_name, r.title as resume_title 
                           FROM conversations c 
                           JOIN users u ON c.jobseeker_id = u.id 
                           JOIN offers o ON c.offer_id = o.id
                           JOIN resumes r ON o.resume_id = r.id
                           WHERE c.id = ? AND c.hr_id = ?");
} else {
    $stmt = $pdo->prepare("SELECT c.*, u.full_name as contact_name, r.title as resume_title 
                           FROM conversations c 
                           JOIN users u ON c.hr_id = u.id 
                           JOIN offers o ON c.offer_id = o.id
                           JOIN resumes r ON o.resume_id = r.id
                           WHERE c.id = ? AND c.jobseeker_id = ?");
}

$stmt->execute([$conversation_id, $user_id]);
$conversation = $stmt->fetch();

if (!$conversation) {
    header("Location: messages.php");
    exit;
}

// Отмечаем сообщения как прочитанные
$stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id != ?");
$stmt->execute([$conversation_id, $user_id]);

// Получаем сообщения
$stmt = $pdo->prepare("SELECT m.*, u.full_name as sender_name 
                       FROM messages m 
                       JOIN users u ON m.sender_id = u.id 
                       WHERE m.conversation_id = ? 
                       ORDER BY m.created_at ASC");
$stmt->execute([$conversation_id]);
$messages = $stmt->fetchAll();

// Получаем реакции для каждого сообщения (если таблица существует)
$reactions_by_message = [];
try {
    if (!empty($messages)) {
        $message_ids = array_column($messages, 'id');
        $placeholders = str_repeat('?,', count($message_ids) - 1) . '?';
        
        $stmt = $pdo->prepare("
            SELECT message_id, reaction, COUNT(*) as count 
            FROM message_reactions 
            WHERE message_id IN ($placeholders)
            GROUP BY message_id, reaction
        ");
        $stmt->execute($message_ids);
        $reactions = $stmt->fetchAll();
        foreach ($reactions as $r) {
            $reactions_by_message[$r['message_id']][] = $r;
        }
    }
} catch (PDOException $e) {
    // Таблица еще не создана
}

// Получаем онлайн статус собеседника (если таблица существует)
$online_status = null;
try {
    $contact_id = $user_type == 'hr' ? $conversation['jobseeker_id'] : $conversation['hr_id'];
    $stmt = $pdo->prepare("SELECT is_online, last_seen FROM user_online_status WHERE user_id = ?");
    $stmt->execute([$contact_id]);
    $online_status = $stmt->fetch();
    
    // Обновляем свой онлайн статус
    $stmt = $pdo->prepare("
        INSERT INTO user_online_status (user_id, last_seen, is_online) 
        VALUES (?, NOW(), 1) 
        ON DUPLICATE KEY UPDATE last_seen = NOW(), is_online = 1
    ");
    $stmt->execute([$user_id]);
} catch (PDOException $e) {
    // Таблица еще не создана
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($conversation['contact_name']) ?> - Чат</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .chat-container {
            height: calc(100vh - 250px);
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px 10px 0 0;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background: #f8f9fa;
        }
        .message {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-end;
        }
        .message.sent {
            justify-content: flex-end;
        }
        .message-bubble {
            max-width: 70%;
            padding: 0.75rem 1rem;
            border-radius: 18px;
            overflow-wrap: break-word;
            word-break: normal;
            hyphens: auto;
        }
        .message.received .message-bubble {
            background: white;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .message.sent .message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }
        .message-time {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
            padding: 0 0.5rem;
        }
        .message.sent .message-time {
            text-align: right;
        }
        .chat-input {
            padding: 1rem;
            background: white;
            border-top: 1px solid #dee2e6;
            border-radius: 0 0 10px 10px;
        }
        .file-attachment {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem;
            border-radius: 8px;
            margin-top: 0.5rem;
            display: inline-block;
        }
        .message.sent .file-attachment {
            background: rgba(255,255,255,0.3);
        }
        #messageInput {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1.25rem;
        }
        #messageInput:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .typing-indicator {
            display: none;
            padding: 0.5rem 1rem;
            color: #6c757d;
            font-style: italic;
        }
        .message-actions {
            display: none;
            position: absolute;
            top: -10px;
            right: 10px;
            background: white;
            border-radius: 20px;
            padding: 0.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .message:hover .message-actions {
            display: flex;
            gap: 0.25rem;
        }
        .btn-action {
            border: none;
            background: transparent;
            padding: 0.25rem 0.5rem;
            cursor: pointer;
            border-radius: 50%;
            transition: background 0.2s;
        }
        .btn-action:hover {
            background: #f0f0f0;
        }
        .reactions-container {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }
        .reaction-badge {
            background: rgba(0,0,0,0.1);
            padding: 0.15rem 0.5rem;
            border-radius: 12px;
            font-size: 0.85rem;
            cursor: pointer;
        }
        .message.sent .reaction-badge {
            background: rgba(255,255,255,0.3);
        }
        .reaction-picker {
            display: none;
            position: absolute;
            background: white;
            border-radius: 25px;
            padding: 0.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
        }
        .reaction-picker span {
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.25rem;
            display: inline-block;
            transition: transform 0.2s;
        }
        .reaction-picker span:hover {
            transform: scale(1.3);
        }
        .message {
            position: relative;
        }
        .message-text textarea {
            background: rgba(255,255,255,0.9);
            border: 2px solid #667eea;
            border-radius: 8px;
            font-family: inherit;
            font-size: inherit;
            line-height: inherit;
        }
        .message.sent .message-text textarea {
            background: rgba(255,255,255,0.95);
            color: #333;
        }
        .edited-label {
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="chat-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">
                                    <i class="fas fa-user-circle me-2"></i>
                                    <?= htmlspecialchars($conversation['contact_name']) ?>
                                    <?php if ($online_status && $online_status['is_online']): ?>
                                        <span class="badge bg-success" style="font-size: 0.7rem;">
                                            <i class="fas fa-circle" style="font-size: 0.5rem;"></i> Онлайн
                                        </span>
                                    <?php elseif ($online_status): ?>
                                        <span class="text-muted" style="font-size: 0.8rem;">
                                            Соңғы рет: <?php
                                            $diff = time() - strtotime($online_status['last_seen']);
                                            if ($diff < 60) echo 'жаңа ғана';
                                            elseif ($diff < 3600) echo floor($diff/60) . ' мин бұрын';
                                            elseif ($diff < 86400) echo floor($diff/3600) . ' сағ бұрын';
                                            else echo date('d.m.Y', strtotime($online_status['last_seen']));
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </h5>
                                <small class="opacity-75">
                                    <i class="fas fa-briefcase me-1"></i>
                                    <?= htmlspecialchars($conversation['resume_title']) ?>
                                </small>
                            </div>
                            <a href="messages.php" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Артқа
                            </a>
                        </div>
                    </div>

                    <div class="chat-container">
                        <div class="chat-messages" id="chatMessages">
                            <?php foreach ($messages as $msg): ?>
                                <div class="message <?= $msg['sender_id'] == $user_id ? 'sent' : 'received' ?>" data-message-id="<?= $msg['id'] ?>">
                                    <div>
                                        <div class="message-bubble" id="msg-<?= $msg['id'] ?>">
                                            <?php if (isset($msg['is_deleted']) && $msg['is_deleted']): ?>
                                                <em class="text-muted"><?= htmlspecialchars($msg['message']) ?></em>
                                            <?php else: ?>
                                                <span class="message-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></span>
                                                <?php if (isset($msg['edited_at']) && $msg['edited_at']): ?>
                                                    <small class="text-muted ms-2">(өңделген)</small>
                                                <?php endif; ?>
                                                
                                                <?php if ($msg['file_path']): ?>
                                                    <div class="file-attachment">
                                                        <i class="fas fa-file me-2"></i>
                                                        <a href="<?= htmlspecialchars($msg['file_path']) ?>" 
                                                           target="_blank" 
                                                           class="text-decoration-none <?= $msg['sender_id'] == $user_id ? 'text-white' : '' ?>">
                                                            <?= htmlspecialchars($msg['file_name']) ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Реакции -->
                                                <?php if (isset($reactions_by_message[$msg['id']])): ?>
                                                    <div class="reactions-container mt-1">
                                                        <?php foreach ($reactions_by_message[$msg['id']] as $r): ?>
                                                            <span class="reaction-badge"><?= $r['reaction'] ?> <?= $r['count'] ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Кнопки действий -->
                                                <div class="message-actions">
                                                    <button class="btn-action" onclick="showReactions(<?= $msg['id'] ?>)" title="Реакция">
                                                        <i class="far fa-smile"></i>
                                                    </button>
                                                    <?php if ($msg['sender_id'] == $user_id && (!isset($msg['is_deleted']) || !$msg['is_deleted'])): ?>
                                                        <button class="btn-action" onclick="editMessage(<?= $msg['id'] ?>)" title="Өңдеу">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn-action" onclick="deleteMessage(<?= $msg['id'] ?>)" title="Жою">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="message-time">
                                            <?= date('H:i', strtotime($msg['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="typing-indicator" id="typingIndicator">
                            <i class="fas fa-ellipsis-h"></i> Жазып жатыр...
                        </div>

                        <!-- Панель выбора реакций -->
                        <div class="reaction-picker" id="reactionPicker">
                            <span onclick="addReaction('👍')">👍</span>
                            <span onclick="addReaction('❤️')">❤️</span>
                            <span onclick="addReaction('😂')">😂</span>
                            <span onclick="addReaction('😮')">😮</span>
                            <span onclick="addReaction('👏')">👏</span>
                            <span onclick="addReaction('🔥')">🔥</span>
                        </div>

                        <div class="chat-input">
                            <form id="messageForm" enctype="multipart/form-data">
                                <div class="input-group">
                                    <label for="fileInput" class="btn btn-outline-secondary" title="Файл тіркеу">
                                        <i class="fas fa-paperclip"></i>
                                    </label>
                                    <input type="file" id="fileInput" name="file" class="d-none" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <input type="text" 
                                           class="form-control" 
                                           id="messageInput" 
                                           name="message"
                                           placeholder="Хабарлама жазыңыз..." 
                                           autocomplete="off"
                                           required>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                <small id="fileName" class="text-muted ms-2"></small>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const conversationId = <?= $conversation_id ?>;
        const userId = <?= $user_id ?>;
        let lastMessageId = <?= !empty($messages) ? end($messages)['id'] : 0 ?>;
        let pollingInterval;

        // Прокрутка вниз
        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Отправка сообщения
        document.getElementById('messageForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('conversation_id', conversationId);
            
            try {
                const response = await fetch('ajax/send_message.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('messageInput').value = '';
                    document.getElementById('fileInput').value = '';
                    document.getElementById('fileName').textContent = '';
                    loadNewMessages();
                } else {
                    alert('Қате: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Хабарлама жіберу кезінде қате орын алды');
            }
        });

        // Загрузка новых сообщений
        async function loadNewMessages() {
            try {
                const response = await fetch(`ajax/get_messages.php?conversation_id=${conversationId}&last_id=${lastMessageId}`);
                const data = await response.json();
                
                if (data.success && data.messages.length > 0) {
                    const chatMessages = document.getElementById('chatMessages');
                    
                    data.messages.forEach(msg => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `message ${msg.sender_id == userId ? 'sent' : 'received'}`;
                        messageDiv.setAttribute('data-message-id', msg.id);
                        
                        let fileHtml = '';
                        if (msg.file_path) {
                            const textClass = msg.sender_id == userId ? 'text-white' : '';
                            fileHtml = `
                                <div class="file-attachment">
                                    <i class="fas fa-file me-2"></i>
                                    <a href="${msg.file_path}" target="_blank" class="text-decoration-none ${textClass}">
                                        ${msg.file_name}
                                    </a>
                                </div>
                            `;
                        }

                        let reactionsHtml = '';
                        if (msg.reactions && msg.reactions.length > 0) {
                            reactionsHtml = `
                                <div class="reactions-container mt-1">
                                    ${msg.reactions.map(r => `<span class="reaction-badge">${r.reaction} ${r.count}</span>`).join('')}
                                </div>
                            `;
                        }

                        let actionsHtml = '';
                        if (!msg.is_deleted || msg.is_deleted === undefined) {
                            actionsHtml = `
                                <div class="message-actions">
                                    <button class="btn-action" onclick="showReactions(${msg.id})" title="Реакция">
                                        <i class="far fa-smile"></i>
                                    </button>
                                    ${msg.sender_id == userId ? `
                                        <button class="btn-action" onclick="editMessage(${msg.id})" title="Өңдеу">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action" onclick="deleteMessage(${msg.id})" title="Жою">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            `;
                        }
                        
                        messageDiv.innerHTML = `
                            <div>
                                <div class="message-bubble" id="msg-${msg.id}">
                                    <span class="message-text">${msg.message.replace(/\n/g, '<br>')}</span>
                                    ${msg.edited_at ? '<small class="text-muted ms-2 edited-label">(өңделген)</small>' : ''}
                                    ${fileHtml}
                                    ${reactionsHtml}
                                    ${actionsHtml}
                                </div>
                                <div class="message-time">
                                    ${new Date(msg.created_at).toLocaleTimeString('kk-KZ', {hour: '2-digit', minute: '2-digit'})}
                                </div>
                            </div>
                        `;
                        
                        chatMessages.appendChild(messageDiv);
                        lastMessageId = msg.id;
                    });
                    
                    scrollToBottom();
                }
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        // Показ имени файла
        document.getElementById('fileInput').addEventListener('change', (e) => {
            const fileName = e.target.files[0]?.name || '';
            document.getElementById('fileName').textContent = fileName ? `Файл: ${fileName}` : '';
        });

        // Long Polling - проверка новых сообщений каждые 3 секунды
        pollingInterval = setInterval(loadNewMessages, 3000);

        // Остановка polling при закрытии страницы
        window.addEventListener('beforeunload', () => {
            clearInterval(pollingInterval);
        });

        // Прокрутка при загрузке
        scrollToBottom();

        // === НОВЫЕ ФУНКЦИИ ===

        let currentMessageId = null;

        // Показать панель реакций
        function showReactions(messageId) {
            currentMessageId = messageId;
            const picker = document.getElementById('reactionPicker');
            const message = document.querySelector(`[data-message-id="${messageId}"]`);
            const rect = message.getBoundingClientRect();
            
            picker.style.display = 'block';
            picker.style.position = 'fixed';
            picker.style.top = (rect.top - 50) + 'px';
            picker.style.left = (rect.left + rect.width / 2 - 150) + 'px';
        }

        // Добавить реакцию
        async function addReaction(reaction) {
            const picker = document.getElementById('reactionPicker');
            picker.style.display = 'none';
            
            if (!currentMessageId) return;
            
            try {
                const response = await fetch('ajax/add_reaction.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `message_id=${currentMessageId}&reaction=${encodeURIComponent(reaction)}`
                });
                
                const data = await response.json();
                if (data.success) {
                    // Обновляем реакции для конкретного сообщения
                    updateMessageReactions(currentMessageId, data.reactions);
                }
            } catch (error) {
                console.error('Error adding reaction:', error);
            }
        }

        // Обновить реакции для сообщения
        function updateMessageReactions(messageId, reactions) {
            const messageEl = document.querySelector(`[data-message-id="${messageId}"]`);
            if (!messageEl) return;
            
            let reactionsContainer = messageEl.querySelector('.reactions-container');
            
            if (reactions.length === 0) {
                if (reactionsContainer) {
                    reactionsContainer.remove();
                }
                return;
            }
            
            if (!reactionsContainer) {
                reactionsContainer = document.createElement('div');
                reactionsContainer.className = 'reactions-container mt-1';
                const messageBubble = messageEl.querySelector('.message-bubble');
                messageBubble.appendChild(reactionsContainer);
            }
            
            reactionsContainer.innerHTML = reactions.map(r => 
                `<span class="reaction-badge">${r.reaction} ${r.count}</span>`
            ).join('');
        }

        // Закрыть панель реакций при клике вне её
        document.addEventListener('click', (e) => {
            const picker = document.getElementById('reactionPicker');
            if (!e.target.closest('.reaction-picker') && !e.target.closest('.btn-action')) {
                picker.style.display = 'none';
            }
        });

        // Удалить сообщение
        async function deleteMessage(messageId) {
            if (!confirm('Хабарламаны жоюға сенімдісіз бе?')) return;
            
            try {
                const response = await fetch('ajax/delete_message.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `message_id=${messageId}`
                });
                
                const data = await response.json();
                if (data.success) {
                    loadNewMessages();
                } else {
                    alert('Қате: ' + data.message);
                }
            } catch (error) {
                console.error('Error deleting message:', error);
            }
        }

        // Редактировать сообщение (inline)
        function editMessage(messageId) {
            const messageEl = document.querySelector(`#msg-${messageId} .message-text`);
            const currentText = messageEl.innerHTML.replace(/<br>/g, '\n');
            
            // Создаем textarea для редактирования
            const textarea = document.createElement('textarea');
            textarea.className = 'form-control';
            textarea.style.resize = 'none';
            textarea.style.minHeight = '40px';
            textarea.value = currentText;
            
            // Создаем кнопки
            const buttonsDiv = document.createElement('div');
            buttonsDiv.className = 'mt-2';
            buttonsDiv.innerHTML = `
                <button class="btn btn-sm btn-success me-2" onclick="saveEdit(${messageId})">
                    <i class="fas fa-check"></i> Сақтау
                </button>
                <button class="btn btn-sm btn-secondary" onclick="cancelEdit(${messageId})">
                    <i class="fas fa-times"></i> Болдырмау
                </button>
            `;
            
            // Сохраняем оригинальный контент
            messageEl.dataset.originalContent = messageEl.innerHTML;
            
            // Заменяем контент на форму редактирования
            messageEl.innerHTML = '';
            messageEl.appendChild(textarea);
            messageEl.appendChild(buttonsDiv);
            
            // Фокус на textarea
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
            
            // Автоматическое изменение высоты
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
            
            // Сохранение по Ctrl+Enter
            textarea.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    saveEdit(messageId);
                }
                if (e.key === 'Escape') {
                    cancelEdit(messageId);
                }
            });
        }

        // Сохранить изменения
        function saveEdit(messageId) {
            const messageEl = document.querySelector(`#msg-${messageId} .message-text`);
            const textarea = messageEl.querySelector('textarea');
            const newText = textarea.value.trim();
            
            if (!newText) {
                alert('Хабарлама бос болмауы керек');
                return;
            }
            
            if (newText === messageEl.dataset.originalContent.replace(/<br>/g, '\n')) {
                cancelEdit(messageId);
                return;
            }
            
            // Показываем индикатор загрузки
            const buttonsDiv = messageEl.querySelector('div');
            buttonsDiv.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';
            
            fetch('ajax/edit_message.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `message_id=${messageId}&message=${encodeURIComponent(newText)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Восстанавливаем контент с новым текстом
                    messageEl.innerHTML = data.new_text.replace(/\n/g, '<br>');
                    
                    // Добавляем метку "отредактировано"
                    const bubble = document.getElementById(`msg-${messageId}`);
                    if (!bubble.querySelector('.edited-label')) {
                        const edited = document.createElement('small');
                        edited.className = 'text-muted ms-2 edited-label';
                        edited.textContent = '(өңделген)';
                        messageEl.appendChild(edited);
                    }
                } else {
                    alert('Қате: ' + data.message);
                    cancelEdit(messageId);
                }
            })
            .catch(error => {
                console.error('Error editing message:', error);
                alert('Хабарламаны өңдеу кезінде қате орын алды');
                cancelEdit(messageId);
            });
        }

        // Отменить редактирование
        function cancelEdit(messageId) {
            const messageEl = document.querySelector(`#msg-${messageId} .message-text`);
            messageEl.innerHTML = messageEl.dataset.originalContent;
            delete messageEl.dataset.originalContent;
        }

        // Отслеживание печати
        let typingTimeout;
        document.getElementById('messageInput').addEventListener('input', (e) => {
            clearTimeout(typingTimeout);
            
            // Отправляем статус "печатает"
            fetch('ajax/update_typing_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `conversation_id=${conversationId}&is_typing=1`
            });
            
            // Через 3 секунды убираем статус
            typingTimeout = setTimeout(() => {
                fetch('ajax/update_typing_status.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `conversation_id=${conversationId}&is_typing=0`
                });
            }, 3000);
        });

        // Проверка статуса "печатает" собеседника
        setInterval(async () => {
            try {
                const response = await fetch(`ajax/check_typing_status.php?conversation_id=${conversationId}`);
                const data = await response.json();
                
                const indicator = document.getElementById('typingIndicator');
                if (data.is_typing) {
                    indicator.style.display = 'block';
                } else {
                    indicator.style.display = 'none';
                }
            } catch (error) {
                console.error('Error checking typing status:', error);
            }
        }, 2000);

        // Обновление онлайн статуса каждые 30 секунд
        setInterval(() => {
            fetch('ajax/update_online_status.php', {method: 'POST'});
        }, 30000);

        // Обновляем статус при закрытии страницы
        window.addEventListener('beforeunload', () => {
            navigator.sendBeacon('ajax/update_online_status.php', new FormData());
        });
    </script>
</body>
</html>
