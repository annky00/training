<?php
// Налаштування підключення до бази даних
$host = 'localhost';
$dbname = 'annkyeve_event_management';
$username = 'annkyeve_annky';
$password = 'Vehxbr77@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Помилка підключення до бази даних: " . $e->getMessage());
}
?>