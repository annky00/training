<?php
session_start();
require_once 'config.php';

// Встановлення часового поясу
date_default_timezone_set('Europe/Kyiv');

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

// Отримання даних події
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
    $stmt->execute([$event_id, $_SESSION['user_id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $_SESSION['error'] = "Подія не знайдена або ви не маєте до неї доступу.";
        header("Location: dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Помилка: " . $e->getMessage();
    header("Location: dashboard.php");
    exit;
}

// Обробка форми
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $description = $_POST['description'] ?? '';
    $type = $_POST['type'] ?? '';
    $priority = $_POST['priority'] ?? '';

    if (empty($title) || empty($event_date) || empty($type) || empty($priority)) {
        $error = "Назва, дата, тип і пріоритет є обов’язковими.";
    } else {
        try {
            $pdo->beginTransaction();

            // Оновлення події
            $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, type = ?, priority = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $description, $event_date, $event_time ?: null, $type, $priority, $event_id, $_SESSION['user_id']]);

            // Перевірка, чи існує сповіщення
            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE event_id = ? AND user_id = ? AND status = 'pending'");
            $stmt->execute([$event_id, $_SESSION['user_id']]);
            $notification = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($notification) {
                // Оновлення часу сповіщення
                $sendTimeStr = $event_time ? $event_date . ' ' . $event_time : $event_date . ' 00:00:00';
                try {
                    $sendTime = new DateTime($sendTimeStr, new DateTimeZone('Europe/Kyiv'));
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Невалідна дата/час для event_id $event_id: $sendTimeStr");
                    $_SESSION['error'] = "Невалідна дата або час події.";
                    header("Location: edit_event.php?id=$event_id");
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE notifications SET send_time = ? WHERE event_id = ? AND user_id = ?");
                $stmt->execute([$sendTime->format('Y-m-d H:i:s'), $event_id, $_SESSION['user_id']]);
                error_log("Оновлено час сповіщення для event_id $event_id: " . $sendTime->format('Y-m-d H:i:s'));
            }

            $pdo->commit();
            $_SESSION['success'] = "Подію успішно відредаговано!";
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Помилка: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управління подіями - Редагувати подію</title>
    <link rel="stylesheet" href="css/forms.css">
    <link rel="icon" type="image/svg+xml" href="img/icons/logo.svg">
</head>
<body>
    <div class="wrapper">
        <div class="left-side">
            <div class="left-side__content">
                <h1 class="left-side__logo">Управління подіями</h1>
                <div class="left-side__login">
                    <h2 class="left-side__title">Редагування</h2>
                    <?php if (isset($error)): ?>
                        <p style="color: #EE5755; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <p style="color: #08BD02; text-align: center;"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
                    <?php endif; ?>
                    <form action="edit_event.php?id=<?php echo $event_id; ?>" method="POST" class="left-side__form form">
                        <label class="form__label">
                            <span class="form__title">Назва</span>
                            <input name="title" required class="form__input" type="text" value="<?php echo htmlspecialchars($event['title']); ?>">
                        </label>
                        <label class="form__label">
                            <span class="form__title">Дата</span>
                            <input name="event_date" required class="form__input" type="date" value="<?php echo htmlspecialchars($event['event_date']); ?>">
                        </label>
                        <label class="form__label">
                            <span class="form__title">Час</span>
                            <input name="event_time" class="form__input" type="time" value="<?php echo htmlspecialchars($event['event_time'] ?: ''); ?>">
                        </label>
                        <label class="form__label">
                            <span class="form__title">Опис</span>
                            <textarea name="description" class="form__input" id="description"><?php echo htmlspecialchars($event['description'] ?: ''); ?></textarea>
                        </label>
                        <div class="form__bot">
                            <div class="form__priority">
                                <label class="form__label radio">
                                    <input required type="radio" name="priority" value="high" class="radio__input" <?php echo $event['priority'] === 'high' ? 'checked' : ''; ?>>
                                    <span class="radio__title radio__title--high">Високий пріоритет</span>
                                </label>
                                <label class="form__label radio">
                                    <input required type="radio" name="priority" value="medium" class="radio__input" <?php echo $event['priority'] === 'medium' ? 'checked' : ''; ?>>
                                    <span class="radio__title radio__title--medium">Середній пріоритет</span>
                                </label>
                                <label class="form__label radio">
                                    <input required type="radio" name="priority" value="low" class="radio__input" <?php echo $event['priority'] === 'low' ? 'checked' : ''; ?>>
                                    <span class="radio__title radio__title--low">Низький пріоритет</span>
                                </label>
                            </div>
                            <div class="form__type">
                                <label class="form__label radio">
                                    <input required type="radio" name="type" value="event" class="radio__input" <?php echo $event['type'] === 'event' ? 'checked' : ''; ?>>
                                    <span class="radio__title radio__title--event">Подія</span>
                                </label>
                                <label class="form__label radio">
                                    <input required type="radio" name="type" value="task" class="radio__input" <?php echo $event['type'] === 'task' ? 'checked' : ''; ?>>
                                    <span class="radio__title radio__title--task">Завдання</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="form__button">Зберегти</button>
                        <a href="dashboard.php" class="form__link form__link--back">Назад</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="right-side"></div>
    </div>
</body>
</html>