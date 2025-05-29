<?php
session_start();
require_once 'config.php';

// Перевірка, чи користувач увійшов
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Отримання подій користувача
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY event_date ASC, event_time ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Запис помилки у лог-файл
    error_log(
        "[" . date('Y-m-d H:i:s') . "] DB error: " . $e->getMessage() . "\n",
        3,
        __DIR__ . '/error.log' 
    );

    // Узагальнене повідомлення для користувача
    $error = "Сталася помилка при завантаженні подій. Будь ласка, спробуйте пізніше.";
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управління подіями</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/svg+xml" href="img/icons/logo.svg">
</head>
<body>
    <div class="wrapper">
        <header class="header">
            <div class="header__container">
                <h1 class="header__logo">Управління подіями</h1>
                <a class="header__link" href="logout.php">Вийти</a>
            </div>
        </header>
        <main class="page">
            <div class="table">
                <div class="table__container">
                    <div class="table__content">
                        <?php if (isset($error)): ?>
                            <p style="color: #EE5755; text-align: center; margin-top: 10px;"><?php echo htmlspecialchars($error); ?></p>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['error'])): ?>
                            <p style="color: #EE5755; text-align: center; margin-top: 10px;"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['success'])): ?>
                            <p style="color: #08BD02; text-align: center; margin-top: 10px;"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
                        <?php endif; ?>
                        <div class="table__list">
                            <div class="table__cards">
                                <?php if (empty($events)): ?>
                                    <p style="text-align: center; color: #232D42; padding: 20px;">У вас поки немає подій.</p>
                                <?php else: ?>
                                    <?php foreach ($events as $event): ?>
                                        <div class="table__card card" data-event-id="<?php echo $event['id']; ?>" data-type="<?php echo htmlspecialchars($event['type']); ?>" data-priority="<?php echo htmlspecialchars($event['priority'] ?: 'low'); ?>" data-date="<?php echo htmlspecialchars($event['event_date']); ?>">
                                            <h2 class="card__name"><?php echo htmlspecialchars($event['title']); ?></h2>
                                            <p class="card__date"><?php echo htmlspecialchars(strftime('%d %B %Y', strtotime($event['event_date']))); ?></p>
                                            <p class="card__time"><?php echo $event['event_time'] ? htmlspecialchars(date('H:i', strtotime($event['event_time']))) : ''; ?></p>
                                            <p class="card__description"><?php echo htmlspecialchars($event['description'] ?: 'Без опису'); ?></p>
                                            <div class="card__bottom">
                                                <p class="card__type card__type--<?php echo htmlspecialchars($event['type']); ?>">
                                                    <?php echo $event['type'] === 'event' ? 'Подія' : 'Завдання'; ?>
                                                </p>
                                                <div class="card__additional">
                                                    <button title="Увімкнути сповіщення" class="card__notif <?php echo $event['is_notified'] ? 'card__notif--active' : ''; ?>"></button>
                                                    <span class="card__prior card__prior--<?php echo htmlspecialchars($event['priority'] ?: 'low'); ?>"></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($events)): ?>
                                <button class="table__button table__button--left"></button>
                                <button class="table__button table__button--right"></button>
                            <?php endif; ?>
                        </div>
                        <div class="table__buttons">
                            <button class="table__button table__button--add"><a href="event.php">+ Додати</a></button>
                            <div class="table__filter">
                                <button class="table__button table__button--filt" id="filterButton" title="Відфільтрувати"></button>
                                <div class="filter__dropdown" id="filterDropdown" style="display: none;">
                                    <ul class="filter__list">
                                        <li class="filter__item" data-filter="all">Усі</li>
                                        <li class="filter__item" data-filter="event">Події</li>
                                        <li class="filter__item" data-filter="task">Завдання</li>
                                        <li class="filter__item" data-filter="high">Високий пріоритет</li>
                                        <li class="filter__item" data-filter="medium">Середній пріоритет</li>
                                        <li class="filter__item" data-filter="low">Низький пріоритет</li>
                                        <li class="filter__item" data-filter="future">Майбутні</li>
                                    </ul>
                                </div>
                            </div>
                            <button class="table__button table__button--edit" id="editModeButton" title="Редагувати"></button>
                            <button class="table__button table__button--del" id="deleteModeButton" title="Видалити"></button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer class="footer">
            <div class="footer__container">
                <p class="footer__text">powered by annky 2025</p>
            </div>
        </footer>
    </div>
    <script src="js/script.js"></script>
</body>
</html>