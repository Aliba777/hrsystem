<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Авторизация қажет']);
    exit;
}

require_once '../config/database.php';

try {
    $image_type = $_POST['image_type'] ?? ''; // 'resume' или 'portfolio'
    
    if (!in_array($image_type, ['resume', 'portfolio'])) {
        throw new Exception('Жарамсыз сурет түрі');
    }
    
    // Проверяем количество уже загруженных изображений
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM user_images WHERE user_id = ? AND image_type = ?");
    $check_stmt->execute([$_SESSION['user_id'], $image_type]);
    $current_count = $check_stmt->fetchColumn();
    
    $max_count = $image_type === 'resume' ? 8 : 20;
    
    if (!isset($_FILES['images'])) {
        throw new Exception('Суреттер жүктелмеді');
    }
    
    $files = $_FILES['images'];
    $file_count = count($files['name']);
    
    if ($current_count + $file_count > $max_count) {
        throw new Exception("Максимум $max_count сурет жүктей аласыз");
    }
    
    // Создаем папку для загрузок если её нет
    $upload_dir = "../uploads/user_" . $_SESSION['user_id'] . "/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $uploaded_files = [];
    
    for ($i = 0; $i < $file_count; $i++) {
        $file_name = $files['name'][$i];
        $file_tmp = $files['tmp_name'][$i];
        $file_size = $files['size'][$i];
        $file_error = $files['error'][$i];
        
        if ($file_error !== UPLOAD_ERR_OK) {
            continue;
        }
        
        // Проверка размера (макс 5MB)
        if ($file_size > 5 * 1024 * 1024) {
            throw new Exception('Сурет өлшемі 5MB-тан аспауы керек');
        }
        
        // Проверка типа файла
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception('Тек сурет файлдарын жүктей аласыз');
        }
        
        // Генерируем уникальное имя файла
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid() . '_' . time() . '.' . $extension;
        $file_path = $upload_dir . $new_file_name;
        
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Сохраняем в базу данных
            $relative_path = "uploads/user_" . $_SESSION['user_id'] . "/" . $new_file_name;
            
            $stmt = $pdo->prepare("
                INSERT INTO user_images (user_id, image_type, image_path, display_order)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $image_type,
                $relative_path,
                $current_count + $i
            ]);
            
            $uploaded_files[] = $relative_path;
        }
    }
    
    if (empty($uploaded_files)) {
        throw new Exception('Суреттер жүктелмеді');
    }
    
    echo json_encode([
        'success' => true,
        'message' => count($uploaded_files) . ' сурет жүктелді',
        'files' => $uploaded_files
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
