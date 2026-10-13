<?php
//подключение к бд и ее возвращение

define("DB_HOST", "127.127.126.32:3306");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "phonebook");

function getdb() {
    static $db = null;
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
        $db = new PDO($dsn, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die('Ошибка подключения к БД: ' . $e->getMessage());
    }

    return $db;
}

function safe($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

?>