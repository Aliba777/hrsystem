<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Получаем все беседы пользователя
if ($user_type == 'hr') {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               u.full_name as contact_name, 
               u.id as contact_id,
               r.title as resume_title,
               (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != ? AND is_read = 0) as unread_count,
               (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message
        FROM conversations c
        JOIN users u ON c.jobseeker_id = u.id
        JOIN offers o ON c.offer_id = o.id
        JOIN resumes r ON o.resume_id = r.id
        WHERE c.hr_id = ?
        ORDER BY c.last_message_at DESC
    ");
    $stmt->execute([$user_id, $user_id]);
} else {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               u.full_name as contact_name,
               u.id as contact_id,
               r.title as resume_title,
               (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != ? AND is_read = 0) as unread_count,
               (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message
        FROM conversations c
        JOIN users u ON c.hr_id = u.id
        JOIN offers o ON c.offer_id = o.id
        JOIN resumes r ON o.resume_id = r.id
        WHERE c.jobseeker_id = ?
        ORDER BY c.last_message_at DESC
    ");
    $stmt->execute([$user_id, $user_id]);
}

$conversations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Хабарламалар - HR Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .conversation-item {
            transition: background-color 0.2s;
            cursor: pointer;
            border-left: 3px solid transparent;
        }
        .conversation-item:hover {
            background-color: #f8f9fa;
        }
        .conversation-item.unread {
            border-left-color: #0d6efd;
            background-color: #f0f7ff;
        }
        .unread-badge {
            min-width: 24px;
            height: 24px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .last-message {
            color: #6c757d;
            font-size: 0.9rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-comments me-2"></i>Хабарламалар</h2>

        <?php if (empty($conversations)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Әзірге хабарламалар жоқ. 
                <?php if ($user_type == 'hr'): ?>
                    Қабылданған офферлер бойынша чат автоматты түрде ашылады.
                <?php else: ?>
                    Оффер қабылдағаннан кейін HR-мен байланыса аласыз.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <?php foreach ($conversations as $conv): ?>
                        <div class="card conversation-item mb-2 <?= $conv['unread_count'] > 0 ? 'unread' : '' ?>" 
                             onclick="window.location.href='chat.php?id=<?= $conv['id'] ?>'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">
                                            <i class="fas fa-user-circle me-2"></i>
                                            <?= htmlspecialchars($conv['contact_name']) ?>
                                        </h5>
                                        <p class="mb-1 text-muted small">
                                            <i class="fas fa-briefcase me-1"></i>
                                            <?= htmlspecialchars($conv['resume_title']) ?>
                                        </p>
                                        <?php if ($conv['last_message']): ?>
                                            <p class="last-message mb-0">
                                                <?= htmlspecialchars(mb_substr($conv['last_message'], 0, 80)) ?>
                                                <?= mb_strlen($conv['last_message']) > 80 ? '...' : '' ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end ms-3">
                                        <small class="text-muted d-block mb-2">
                                            <?php
                                            $time_diff = time() - strtotime($conv['last_message_at']);
                                            if ($time_diff < 60) {
                                                echo 'Жаңа ғана';
                                            } elseif ($time_diff < 3600) {
                                                echo floor($time_diff / 60) . ' мин бұрын';
                                            } elseif ($time_diff < 86400) {
                                                echo floor($time_diff / 3600) . ' сағ бұрын';
                                            } else {
                                                echo date('d.m.Y', strtotime($conv['last_message_at']));
                                            }
                                            ?>
                                        </small>
                                        <?php if ($conv['unread_count'] > 0): ?>
                                            <span class="badge bg-primary unread-badge">
                                                <?= $conv['unread_count'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
