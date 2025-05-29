<?php
session_start();
require_once 'config.php';

// Перевірка, чи користувач увійшов
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Перевірка, чи передано ID події
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Невірний ідентифікатор події.";
    header("Location: dashboard.php");
    exit;
}

$event_id = $_GET['id'];

try {
    $pdo->beginTransaction();

    // Перевірка, чи подія належить користувачу
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
    $stmt->execute([$event_id, $_SESSION['user_id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $pdo->rollBack();
        $_SESSION['error'] = "Подія не знайдена або ви не маєте до неї доступу.";
        header("Location: dashboard.php");
        exit;
    }

    // Видалення сповіщень, пов’язаних із подією
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$event_id, $_SESSION['user_id']]);

    // Видалення події
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
    $stmt->execute([$event_id, $_SESSION['user_id']]);

    $pdo->commit();
    $_SESSION['success'] = "Подію та пов’язані сповіщення успішно видалено!";
    header("Location: dashboard.php");
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Помилка: " . $e->getMessage();
    header("Location: dashboard.php");
    exit;
}
?>