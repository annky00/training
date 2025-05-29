<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Будь ласка, заповніть усі поля.";
    } elseif ($password !== $confirm_password) {
        $error = "Паролі не збігаються.";
    } else {
        try {
            // Перевірка, чи email уже існує
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Ця електронна пошта вже зареєстрована.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                $stmt->execute([$email, $hashed_password]);
                $_SESSION['success'] = "Реєстрація успішна! Увійдіть, будь ласка.";
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
    <title>Управління подіями - Реєстрація</title>
    <link rel="stylesheet" href="css/forms.css">
    <link rel="icon" type="image/svg+xml" href="img/icons/logo.svg">
</head>
<body>
    <div class="wrapper">
        <div class="right-side right-side--rotate"></div>
        <div class="left-side">
            <div class="left-side__content">
                <h1 class="left-side__logo">Управління подіями</h1>
                <div class="left-side__login">
                    <h2 class="left-side__title">Зареєструватись</h2>
                    <?php if (isset($error)): ?>
                        <p style="color: #EE5755; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <p style="color: #08BD02; text-align: center;"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
                    <?php endif; ?>
                    <form action="register.php" method="POST" class="left-side__form form">
                        <label class="form__label">
                            <span class="form__title">Електронна пошта</span>
                            <input name="email" required class="form__input" type="email">
                        </label>
                        <label class="form__label">
                            <span class="form__title">Пароль</span>
                            <input name="password" required class="form__input" type="password">
                        </label>
                        <label class="form__label">
                            <span class="form__title">Підтвердити пароль</span>
                            <input name="confirm_password" required class="form__input" type="password">
                        </label>
                        <button type="submit" class="form__button">Зареєструватись</button>
                    </form>
                    <div class="left-side__bottom">
                        <span class="left-side__text">Вже є обліковий запис?</span>
                        <a href="login.php" class="form__link">Увійти</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>