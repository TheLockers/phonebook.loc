<?php
// validation-form/auth.php | Авторизация

session_start();
require_once '../config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Некорректная почта";
    header("Location: /index.php");
    exit();
} else if (mb_strlen($email) < 5 || mb_strlen($email) > 50) {
    $_SESSION['error'] = "Недопустимая длина логина";
    header("Location: /index.php");
    exit();
} else if (empty($password)) {
    $_SESSION['error'] = "Пароль не может быть пустым";
    header("Location: /index.php");
    exit();
}

$mysql = null;
$stmt = null;

$host = '127.127.126.32';
$db_user = 'root';
$db_pass = '';
$db_name = 'phonebook';
$port = 3306;
$socket = 'MySQL-8.4';

try {

    $mysql = new mysqli($host, $db_user, $db_pass, $db_name, $port, $socket);

    if ($mysql->connect_error) {
        throw new Exception("Ошибка подключения: " . $mysql->connect_error);
    }

    $stmt = $mysql->prepare("SELECT id, first_name, last_name, email, password_hash, is_admin FROM `contacts` WHERE `email` = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $_SESSION['error'] = "Пользователь не найден";
        header("Location: /index.php");
        exit();
    }

    if (password_verify($password, $user['password_hash'])) {
        // Устанавливаем сессионные переменные
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
        $_SESSION['is_admin'] = (bool) $user['is_admin'];
        $_SESSION['authenticated'] = true;

        // Удаляем сообщение об ошибке, если оно было
        unset($_SESSION['error']);

        header("Location: /index.php");
        exit();
    } else {
        $_SESSION['error'] = "Неверный пароль";
        header("Location: /index.php");
        exit();
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Ошибка: " . $e->getMessage();
    header("Location: /index.php");
    exit();
} finally {
    if ($stmt)
        $stmt->close();
    if ($mysql)
        $mysql->close();
}
?>