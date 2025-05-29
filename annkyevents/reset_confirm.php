<?php
session_start();
require_once 'config.php';

$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $error = "Будь ласка, заповніть усі поля.";
    } elseif ($password !== $confirm_password) {
        $error = "Паролі не збігаються.";
    } else {
        try {
            // Перевірка токена
            $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires > NOW()");
            $stmt->execute([$token]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reset) {
                $error = "Недійсний або прострочений токен.";
            } else {
                // Оновлення пароля
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $reset['email']]);

                // Видалення токена
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->execute([$token]);

                $_SESSION['success'] = "Пароль успішно змінено. Увійдіть, будь ласка.";
                header("Location: login.php");
                exit;
            }
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
    <title>Управління подіями - Скидання пароля</title>
    <link rel="stylesheet" href="css/forms.css">
    <link rel="icon" type="image/svg+xml" href="img/icons/logo.svg">
</head>
<body>
    <div class="wrapper">
        <div class="left-side">
            <div class="left-side__content">
                <h1 class="left-side__logo">Управління подіями</h1>
                <div class="left-side__login">
                    <h2 class="left-side__title">Скинути пароль</h2>
                    <?php if (isset($error)): ?>
                        <p style="color: #EE5755; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <form action="reset_confirm.php" method="POST" class="left-side__form form">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <label class="form__label">
                            <span class="form__title">Пароль</span>
                            <input name="password" required class="form__input" type="password">
                        </label>
                        <label class="form__label">
                            <span class="form__title">Підтвердити пароль</span>
                            <input name="confirm_password" required class="form__input" type="password">
                        </label>
                        <button type="submit" class="form__button">Скинути</button>
                    </form>
                    <div class="left-side__bottom">
                        <span class="left-side__text">Повернутися до входу?</span>
                        <a href="login.php" class="form__link">Увійти</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="right-side"></div>
    </div>
</body>
</html>