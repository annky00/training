<?php
require_once 'config.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Встановлення часового поясу
date_default_timezone_set('Europe/Kyiv');

try {
    // Отримання сповіщень, які потрібно надіслати
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE status = 'pending' AND send_time <= NOW()");
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Знайдено сповіщень для надсилання: " . count($notifications));

    if (empty($notifications)) {
        error_log("Немає сповіщень для надсилання. Перевірте таблицю notifications.");
    }

    foreach ($notifications as $notification) {
        // Отримання даних події
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$notification['event_id']]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            error_log("Подія не знайдена для notification_id {$notification['id']}");
            $stmt = $pdo->prepare("UPDATE notifications SET status = 'failed' WHERE id = ?");
            $stmt->execute([$notification['id']]);
            continue;
        }

        // Отримання email користувача
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$notification['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || empty($user['email'])) {
            error_log("Користувач або email не знайдені для user_id {$notification['user_id']}");
            $stmt = $pdo->prepare("UPDATE notifications SET status = 'failed' WHERE id = ?");
            $stmt->execute([$notification['id']]);
            continue;
        }

        // Налаштування PHPMailer
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 2; // Увімкнення детального логування SMTP
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer [рівень $level]: $str");
        };

        $retryCount = 0;
        $maxRetries = 2;

        while ($retryCount <= $maxRetries) {
            try {
                $mail->isSMTP();
                $mail->Host = 'uashared42.twinservers.net';
                $mail->SMTPAuth = true;
                $mail->Username = 'info@annkyevents.pp.ua';
                $mail->Password = ПАРОЛЬ;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('info@annkyevents.pp.ua', 'Управління подіями');
                $mail->addAddress($user['email']);
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = 'Сповіщення про подію';

                // Спрощений HTML і CSS для сумісності з поштовими клієнтами
                $type = $event['type'] === 'event' ? 'Подія' : 'Завдання';
                $time = $event['event_time'] ? date('H:i', strtotime($event['event_time'])) : '00:00';
                $htmlBody = "
                    <div style='border: 3px solid #FFF; background: #FFF; font-family: Arial, sans-serif; font-size: 16px; padding: 20px; max-width: 300px; border-radius: 10px;'>
                        <h2 style='color: #222; font-weight: bold;'>" . htmlspecialchars($event['title']) . "</h2>
                        <p style='color: #D5A2A2;'><strong>Дата:</strong> " . strftime('%d %B %Y', strtotime($event['event_date'])) . "</p>
                        <p style='color: #D5A2A2;'><strong>Час:</strong> " . $time . "</p>
                        <p style='background: #f0e0e3; padding: 10px; border-radius: 10px;'><strong>Опис:</strong> " . htmlspecialchars($event['description'] ?: 'Без опису') . "</p>
                        <p style='color: " . ($event['type'] === 'event' ? '#7E1FD0' : '#C98BFE') . ";'><strong>Тип:</strong> " . $type . "</p>
                    </div>
                ";
                $mail->Body = $htmlBody;

                $mail->AltBody = "Сповіщення про подію:\n" .
                    "Назва: " . $event['title'] . "\n" .
                    "Дата: " . strftime('%d %B %Y', strtotime($event['event_date'])) . "\n" .
                    "Час: " . $time . "\n" .
                    "Опис: " . ($event['description'] ?: 'Без опису') . "\n" .
                    "Тип: " . $type;

                error_log("Надсилається HTML: " . $htmlBody); // Логування HTML для відлагодження
                $mail->send();
                $stmt = $pdo->prepare("UPDATE notifications SET status = 'sent' WHERE id = ?");
                $stmt->execute([$notification['id']]);
                error_log("Сповіщення успішно надіслано для notification_id {$notification['id']}, email: {$user['email']}");
                break; // Вихід із циклу після успішної відправки
            } catch (Exception $e) {
                $retryCount++;
                $errorMessage = $e->getMessage();
                error_log("Помилка надсилання сповіщення для notification_id {$notification['id']} (спроба $retryCount): $errorMessage");
                if ($retryCount > $maxRetries) {
                    $stmt = $pdo->prepare("UPDATE notifications SET status = 'failed' WHERE id = ?");
                    $stmt->execute([$notification['id']]);
                    error_log("Надсилання не вдалося після $maxRetries спроб для notification_id {$notification['id']}: $errorMessage");
                } else {
                    sleep(1); // Затримка перед повторною спробою
                }
            }
        }
    }
} catch (PDOException $e) {
    $errorMessage = $e->getMessage();
    error_log("Помилка бази даних у send_notifications.php: $errorMessage");
}
?>
