<?php
session_start();
require_once 'config.php';

// Перевірка, чи користувач увійшов
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
            $stmt = $pdo->prepare("INSERT INTO events (user_id, title, description, event_date, event_time, type, priority) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $event_date, $event_time ?: null, $type, $priority]);
            $_SESSION['success'] = "Подію успішно додано!";
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
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
    <title>Управління подіями - Додати подію</title>
    <link rel="stylesheet" href="css/forms.css">
    <link rel="icon" type="image/svg+xml" href="img/icons/logo.svg">
</head>
<body>
    <div class="wrapper">
        <div class="left-side">
            <div class="left-side__content">
                <h1 class="left-side__logo">Управління подіями</h1>
                <div class="left-side__login">
                    <h2 class="left-side__title">Подія чи завдання?</h2>
                    <?php if (isset($error)): ?>
                        <p style="color: #EE5755; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <p style="color: #08BD02; text-align: center;"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
                    <?php endif; ?>
                    <form action="event.php" method="POST" class="left-side__form form">
                        <label class="form__label">
                            <span class="form__title">Назва</span>
                            <input name="title" required class="form__input" type="text">
                        </label>
                        <label class="form__label">
                            <span class="form__title">Дата</span>
                            <input name="event_date" required class="form__input" type="date">
                        </label>
                        <label class="form__label">
                            <span class="form__title">Час</span>
                            <input name="event_time" class="form__input" type="time">
                        </label>
                        <label class="form__label">
                            <span class="form__title">Опис</span>
                            <textarea name="description" class="form__input" id="description"></textarea>
                        </label>
                        <div class="form__bot">
                            <div class="form__priority">
                                <label class="form__label radio">
                                    <input required type="radio" name="priority" value="high" class="radio__input" checked>
                                    <span class="radio__title radio__title--high">Високий пріоритет</span>
                                </label>
                                <label class="form__label radio">
                                    <input required type="radio" name="priority" value="medium" class="radio__input">
                                    <span class="radio__title radio__title--medium">Середній пріоритет</span>
                                </label>
                                <label class="form__label radio">
                                    <input required type="radio" name="priority" value="low" class="radio__input">
                                    <span class="radio__title radio__title--low">Низький пріоритет</span>
                                </label>
                            </div>
                            <div class="form__type">
                                <label class="form__label radio">
                                    <input required type="radio" name="type" value="event" class="radio__input" checked>
                                    <span class="radio__title radio__title--event">Подія</span>
                                </label>
                                <label class="form__label radio">
                                    <input required type="radio" name="type" value="task" class="radio__input">
                                    <span class="radio__title radio__title--task">Завдання</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="form__button">Додати</button>
                        <a href="dashboard.php" class="form__link form__link--back">Назад</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="right-side"></div>
    </div>
</body>
</html>