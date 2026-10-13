<?php
//обновление данных, перезапись в бд

session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Проверяем авторизацию
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

// Получаем данные
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID не указан']);
    exit;
}

try {
    $db = getdb();

    // Проверяем права доступа (если нужно)
    $currentUserId = $_SESSION['user_id'] ?? null;
    $isAdmin = $_SESSION['is_admin'] ?? false;

    if (!$isAdmin && $data['id'] != $currentUserId) {
        echo json_encode(['success' => false, 'message' => 'Нет прав для редактирования']);
        exit;
    }

    // Подготовка данных для фото
    $photoData = null;
    $photoAction = $data['photo_action'] ?? null;

    if ($photoAction === 'update' && isset($data['photo']) && !empty($data['photo'])) {
        // Обновление фото
        $photoData = $data['photo'];

        // Если есть префикс data:image/..., удаляем его
        if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $matches)) {
            $photoData = substr($photoData, strpos($photoData, ',') + 1);
        }

        // Декодируем base64
        $photoData = base64_decode($photoData);
    } elseif ($photoAction === 'delete') {
        // Удаление фото - устанавливаем NULL
        $photoData = null;
    }
    // Если $photoAction === 'keep' или не указан - оставляем старое фото

    // SQL запрос с разными вариантами для фото
    if ($photoAction === 'update' && $photoData !== null) {
        // Обновляем с новым фото
        $sql = "UPDATE contacts SET 
                last_name = :last_name,
                first_name = :first_name,
                middle_name = :middle_name,
                corps = :corps,
                office_number = :office_number,
                internal_phone_number = :internal_phone_number,
                landline = :landline,
                mobile_phone_number = :mobile_phone_number,
                email = :email,
                photo = :photo,
                updated_at = NOW()
                WHERE id = :id";
    } elseif ($photoAction === 'delete') {
        // Удаляем фото (устанавливаем NULL)
        $sql = "UPDATE contacts SET 
                last_name = :last_name,
                first_name = :first_name,
                middle_name = :middle_name,
                corps = :corps,
                office_number = :office_number,
                internal_phone_number = :internal_phone_number,
                landline = :landline,
                mobile_phone_number = :mobile_phone_number,
                email = :email,
                photo = NULL,
                updated_at = NOW()
                WHERE id = :id";
    } else {
        // Оставляем старое фото без изменений
        $sql = "UPDATE contacts SET 
                last_name = :last_name,
                first_name = :first_name,
                middle_name = :middle_name,
                corps = :corps,
                office_number = :office_number,
                internal_phone_number = :internal_phone_number,
                landline = :landline,
                mobile_phone_number = :mobile_phone_number,
                email = :email,
                updated_at = NOW()
                WHERE id = :id";
    }

    $stmt = $db->prepare($sql);

    $params = [
        ':last_name' => $data['last_name'] ?? '',
        ':first_name' => $data['first_name'] ?? '',
        ':middle_name' => $data['middle_name'] ?? null,
        ':corps' => $data['corps'] ?? null,
        ':office_number' => $data['office_number'] ?? null,
        ':internal_phone_number' => $data['internal_phone_number'] ?? null,
        ':landline' => $data['landline'] ?? null,
        ':mobile_phone_number' => $data['mobile_phone_number'] ?? null,
        ':email' => $data['email'] ?? null,
        ':id' => $data['id']
    ];

    if ($photoAction === 'update' && $photoData !== null) {
        $params[':photo'] = $photoData;
    }

    $result = $stmt->execute($params);

    if ($result) {
        // Обработка пароля если есть
        if (isset($data['new_password']) && !empty($data['new_password'])) {
            if (isset($data['confirm_password']) && $data['new_password'] === $data['confirm_password']) {
                $password_hash = password_hash($data['new_password'], PASSWORD_DEFAULT);
                $stmt2 = $db->prepare("UPDATE contacts SET password_hash = :password_hash WHERE id = :id");
                $stmt2->execute([
                    ':password_hash' => $password_hash,
                    ':id' => $data['id']
                ]);
            }
        }

        // Обработка админ-статуса если есть
        if (isset($data['is_admin']) && $isAdmin && $data['id'] != $currentUserId) {
            $stmt3 = $db->prepare("UPDATE contacts SET is_admin = :is_admin WHERE id = :id");
            $stmt3->execute([
                ':is_admin' => $data['is_admin'] ? 1 : 0,
                ':id' => $data['id']
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Данные сохранены']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка сохранения']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка БД: ' . $e->getMessage()]);
}
?>