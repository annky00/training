<?php
session_start();
require_once 'config.php';

// Перевірка, чи користувач уже увійшов
if (isset($_SESSION['user_id'])) {
    unset($_SESSION['success']); 
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Будь ласка, заповніть усі поля.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Невірна електронна пошта або пароль.";
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
    <title>Управління подіями - Логін</title>
    <link rel="stylesheet" href="css/forms.css">
    <link rel="icon" type="image/svg+xml" href="img/icons/logo.svg">
</head>
<body>
    <div class="wrapper">
        <div class="left-side">
            <div class="left-side__content">
                <h1 class="left-side__logo">Управління подіями</h1>
                <div class="left-side__login">
                    <h2 class="left-side__title">Увійти</h2>
                    <?php if (isset($error)): ?>
                        <p style="color: #EE5755; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <p style="color: #08BD02; text-align: center;"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
                    <?php endif; ?>
                    <form action="login.php" method="POST" class="left-side__form form">
                        <label class="form__label">
                            <span class="form__title">Електронна пошта</span>
                            <input name="email" required class="form__input" type="email">
                        </label>
                        <label class="form__label">
                            <span class="form__title">Пароль</span>
                            <input name="password" required class="form__input" type="password">
                        </label>
                        <a href="reset.php" class="form__link">Забули пароль?</a>
                        <button type="submit" class="form__button">Увійти</button>
                    </form>
                    <div class="left-side__bottom">
                        <span class="left-side__text">Не маєте акаунту?</span>
                        <a href="register.php" class="form__link">Клацніть тут, щоб зареєструватись.</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="right-side"></div>
    </div>
</body>
</html>