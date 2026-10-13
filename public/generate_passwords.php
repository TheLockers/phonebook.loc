<?php
/// generate_passwords.php (уже было использовано) повторно лучше не запускать

require_once 'config.php';

echo "<!DOCTYPE html><html><head><title>Генерация паролей</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    .password { font-weight: bold; color: #d00; font-family: monospace; }
    .hash { font-size: 10px; word-break: break-all; font-family: monospace; }
    .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
    .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px; }
    .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px; }
    .admin { color: #d00; font-weight: bold; }
</style>";
echo "</head><body>";

echo "<h2>Генерация паролей для пользователей из таблицы contacts</h2>";

function generateRandomPassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

try {
    $db = getdb();
    
    // Проверяем, есть ли столбец password_hash в таблице contacts
    $stmt = $db->query("SHOW COLUMNS FROM contacts LIKE 'password_hash'");
    $hasPasswordColumn = $stmt->fetch();
    
    if (!$hasPasswordColumn) {
        // Добавляем столбец если его нет
        $db->exec("ALTER TABLE contacts ADD COLUMN password_hash VARCHAR(255) DEFAULT NULL");
        echo "<div class='warning'>✓ Добавлен столбец password_hash в таблицу contacts</div>";
    }
    
    // Проверяем, есть ли столбец is_admin
    $stmt = $db->query("SHOW COLUMNS FROM contacts LIKE 'is_admin'");
    $hasAdminColumn = $stmt->fetch();
    
    if (!$hasAdminColumn) {
        // Добавляем столбец если его нет
        $db->exec("ALTER TABLE contacts ADD COLUMN is_admin TINYINT(1) DEFAULT 0");
        echo "<div class='warning'>✓ Добавлен столбец is_admin в таблицу contacts</div>";
    }
    
    // Получаем всех пользователей из таблицы contacts
    $stmt = $db->query("SELECT id, first_name, last_name, email, is_admin FROM contacts ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<div class='error'>Таблица contacts пуста!</div>";
    } else {
        echo "<div class='success'>Найдено " . count($users) . " пользователей в таблице contacts</div>";
        
        // Генерируем пароли для всех пользователей
        echo "<table>";
        echo "<tr><th>ID</th><th>ФИО</th><th>Email</th><th>Статус</th><th>Пароль</th><th>Хеш пароля</th><th>Результат</th></tr>";
        
        $generatedPasswords = [];
        
        foreach ($users as $user) {
            // Генерируем пароль
            $password = generateRandomPassword(10);
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Определяем полное имя
            $full_name = trim($user['last_name'] . ' ' . $user['first_name']);
            
            // Проверяем, является ли пользователь администратором
            // Сделаем первого пользователя админом, если не указано иное
            $is_admin = $user['is_admin'] ?? 0;
            if ($user['id'] == 1 && empty($is_admin)) {
                $is_admin = 1;
            }
            
            // Обновляем пароль в базе данных
            $updateStmt = $db->prepare("UPDATE contacts SET password_hash = ?, is_admin = ? WHERE id = ?");
            $updateStmt->execute([$password_hash, $is_admin, $user['id']]);
            
            // Сохраняем для отображения
            $generatedPasswords[] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'full_name' => $full_name,
                'password' => $password,
                'password_hash' => $password_hash,
                'is_admin' => $is_admin
            ];
            
            // Отображаем в таблице
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$full_name}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>" . ($is_admin ? '<span class="admin">Администратор</span>' : 'Пользователь') . "</td>";
            echo "<td class='password'><strong>{$password}</strong></td>";
            echo "<td class='hash'>" . substr($password_hash, 0, 50) . "...</td>";
            echo "<td style='color: green;'>✓ Успешно</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Показываем итоговую таблицу с паролями
        echo "<div class='warning'>";
        echo "<h3>⚠️ ВАЖНО! Сохраните эти пароли!</h3>";
        echo "<p>Пароли будут показаны только один раз и не сохранятся в базе данных в открытом виде.</p>";
        echo "</div>";
        
        echo "<h3>Список паролей для входа:</h3>";
        echo "<table>";
        echo "<tr><th>Email</th><th>Пароль</th><th>Роль</th><th>ID</th></tr>";
        
        foreach ($generatedPasswords as $user) {
            $roleText = $user['is_admin'] ? '<span class="admin">Администратор</span>' : 'Пользователь';
            echo "<tr>";
            echo "<td>{$user['email']}</td>";
            echo "<td class='password'><strong>{$user['password']}</strong></td>";
            echo "<td>{$roleText}</td>";
            echo "<td>{$user['id']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Сохраняем пароли в текстовый файл
        $passwordsFile = 'user_passwords.txt';
        $content = "Сгенерированные пароли для входа в систему\n";
        $content .= "Дата генерации: " . date('Y-m-d H:i:s') . "\n";
        $content .= "Всего пользователей: " . count($generatedPasswords) . "\n\n";
        
        foreach ($generatedPasswords as $user) {
            $roleText = $user['is_admin'] ? 'Администратор' : 'Пользователь';
            $content .= "ID: {$user['id']}\n";
            $content .= "Email: {$user['email']}\n";
            $content .= "ФИО: {$user['full_name']}\n";
            $content .= "Пароль: {$user['password']}\n";
            $content .= "Роль: {$roleText}\n";
            $content .= "---\n";
        }
        
        if (file_put_contents($passwordsFile, $content)) {
            echo "<div class='success'>";
            echo "✓ Пароли сохранены в файл: <strong>{$passwordsFile}</strong><br>";
            echo "<small>Рекомендуется скачать и удалить этот файл с сервера</small>";
            echo "</div>";
            
            // Ссылка для скачивания
            echo "<p><a href='{$passwordsFile}' download style='padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>⬇ Скачать пароли</a></p>";
        }
        
        // Создаем тестового пользователя с простым паролем для демонстрации
        echo "<div class='warning'>";
        echo "<h3>Тестовый доступ:</h3>";
        echo "<p>Для тестирования системы можно использовать:</p>";
        echo "<ul>";
        echo "<li><strong>Администратор:</strong> i.petrov@company.ru с сгенерированным паролем</li>";
        echo "<li><strong>Пользователь:</strong> m.sidorova@company.ru с сгенерированным паролем</li>";
        echo "</ul>";
        echo "</div>";
        
        // Кнопка для перехода на главную
        echo "<p><a href='index.php' style='padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Перейти на главную для входа</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>Ошибка базы данных:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>