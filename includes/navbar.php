<?php
// Определяем префикс пути в зависимости от того, откуда подключен navbar
$prefix = (strpos($_SERVER['SCRIPT_NAME'], '/jobseeker/') !== false || 
           strpos($_SERVER['SCRIPT_NAME'], '/hr/') !== false || 
           strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' : '';
?>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="<?= $prefix ?>dashboard.php">
            <i class="fas fa-handshake me-2"></i>HR Connect
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $prefix ?>dashboard.php"><i class="fas fa-home me-2"></i>Басты бет</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $prefix ?>jobseeker/browse_vacancies.php"><i class="fas fa-briefcase me-2"></i>Вакансиялар</a>
                </li>
                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'hr'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $prefix ?>resumes.php"><i class="fas fa-file-alt me-2"></i>Резюмелер</a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'job_seeker'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $prefix ?>jobseeker/my_resumes.php"><i class="fas fa-folder me-2"></i>Менің резюмелерім</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $prefix ?>jobseeker/my_applications.php"><i class="fas fa-paper-plane me-2"></i>Өтінімдерім</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= $prefix ?>messages.php">
                            <i class="fas fa-comments me-2"></i>Хабарламалар
                            <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" id="unreadBadge" style="display: none;">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $prefix ?>profile.php"><i class="fas fa-user me-2"></i>Профиль</a>
                    </li>
                <?php elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'hr'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $prefix ?>hr/my_vacancies.php"><i class="fas fa-list me-2"></i>Менің вакансияларым</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $prefix ?>hr/my_offers.php"><i class="fas fa-envelope me-2"></i>Менің офферлерім</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= $prefix ?>messages.php">
                            <i class="fas fa-comments me-2"></i>Хабарламалар
                            <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" id="unreadBadge" style="display: none;">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $prefix ?>profile.php"><i class="fas fa-user me-2"></i>Профиль</a>
                    </li>
                <?php elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $prefix ?>admin/dashboard.php"><i class="fas fa-shield-alt me-2"></i>Әкімші панелі</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $prefix ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Шығу</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php if (isset($_SESSION['user_id'])): ?>
<script>
// Обновление счетчика непрочитанных сообщений
async function updateUnreadCount() {
    try {
        const response = await fetch('<?= $prefix ?>ajax/get_unread_count.php');
        const data = await response.json();
        
        if (data.success && data.count > 0) {
            const badge = document.getElementById('unreadBadge');
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = 'inline-block';
            }
        } else {
            const badge = document.getElementById('unreadBadge');
            if (badge) {
                badge.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error updating unread count:', error);
    }
}

// Обновляем счетчик при загрузке и каждые 10 секунд
updateUnreadCount();
setInterval(updateUnreadCount, 10000);
</script>

<!-- Подключение AI ассистента для авторизованных пользователей -->
<?php 
// Определяем правильный путь к файлу ai_chat_modal.php
$ai_chat_path = $prefix . 'includes/ai_chat_modal.php';
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/hr-connect/' . $ai_chat_path)) {
    include $ai_chat_path;
} elseif (file_exists(__DIR__ . '/ai_chat_modal.php')) {
    include __DIR__ . '/ai_chat_modal.php';
} else {
    // Пробуем альтернативные пути
    $possible_paths = [
        __DIR__ . '/../includes/ai_chat_modal.php',
        __DIR__ . '/../../includes/ai_chat_modal.php',
        $_SERVER['DOCUMENT_ROOT'] . '/hr-connect/includes/ai_chat_modal.php'
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            include $path;
            break;
        }
    }
}
?>
<?php endif; ?>