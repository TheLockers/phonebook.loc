<?php
// exit.php | Выход из аккаунта

session_start();

// Очищаем все сессионные переменные
$_SESSION = [];

// Если установлены куки для авторизации, удаляем их
if (isset($_COOKIE['user'])) {
    setcookie('user', '', time() - 3600, "/");
} 

// Уничтожаем сессию
if (session_destroy()) {
    // Перенаправляем на главную страницу
    header('Location: /index.php');
    exit;
} else {
    // Если не удалось уничтожить сессию, все равно перенаправляем
    header('Location: /index.php');
    exit;
}
?>