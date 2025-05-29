<?php
session_start();
require_once 'config.php';

// Встановлення часового поясу
date_default_timezone_set('Europe/Kyiv');

// Перевірка авторизації
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Не авторизовано']);
    exit;
}

$eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$activate = isset($_GET['activate']) ? filter_var($_GET['activate'], FILTER_VALIDATE_BOOLEAN) : false;

if ($eventId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Недійсний ID події']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Перевірка події
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
    $stmt->execute([$eventId, $_SESSION['user_id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $pdo->rollBack();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Подія не знайдена або ви не маєте до неї доступу']);
        exit;
    }

    // Формування часу сповіщення (віднімання 15 хвилин і округлення до 15-хвилинного інтервалу)
    $sendTimeStr = $event['event_time'] ? $event['event_date'] . ' ' . $event['event_time'] : $event['event_date'] . ' 00:00:00';
    try {
        $sendTime = new DateTime($sendTimeStr, new DateTimeZone('Europe/Kyiv'));
        $sendTime->modify('-15 minutes'); // Віднімання 15 хвилин
        // Округлення до попереднього 15-хвилинного інтервалу
        $minutes = $sendTime->format('i');
        $sendTime->modify('-' . ($minutes % 15) . ' minutes');
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Невалідна дата/час для event_id $eventId: $sendTimeStr");
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Невалідна дата або час події']);
        exit;
    }

    if ($activate) {
        // Видалення попереднього сповіщення, якщо існує
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$eventId, $_SESSION['user_id']]);

        // Додавання нового сповіщення
        $stmt = $pdo->prepare("INSERT INTO notifications (event_id, user_id, send_time, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$eventId, $_SESSION['user_id'], $sendTime->format('Y-m-d H:i:s')]);

        // Оновлення статусу події
        $stmt = $pdo->prepare("UPDATE events SET is_notified = 1 WHERE id = ?");
        $stmt->execute([$eventId]);

        $message = 'Сповіщення заплановано на ' . $sendTime->format('d.m.Y H:i');
        error_log("Сповіщення заплановано для event_id $eventId: $message");
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        // Видалення сповіщення
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$eventId, $_SESSION['user_id']]);

        // Оновлення статусу події
        $stmt = $pdo->prepare("UPDATE events SET is_notified = 0 WHERE id = ?");
        $stmt->execute([$eventId]);

        $message = 'Сповіщення скасовано';
        error_log("Сповіщення скасовано для event_id $eventId");
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $message]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    $message = 'Помилка: ' . $e->getMessage();
    error_log("Помилка в schedule_notification.php для event_id $eventId: $message");
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $message]);
}
?>