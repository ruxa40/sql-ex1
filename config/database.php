<?php
$host = 'localhost';
$user = 'root'; // Пользователь базы данных
$pass = ''; // Пароль root или другого пользователя
$db = 'general'; // Имя базы данных

// Подключение через MySQLi
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>
