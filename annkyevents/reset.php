<?php
session_start();
require_once 'config.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitted'])) {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        $error = "Будь ласка, введіть електронну пошту.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Невірний формат електронної пошти.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = "Користувача з такою електронною поштою не знайдено.";
            } else {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+24 hours')); // Збільшено до 24 годин

                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token, $expires]);
                echo "Збережений токен: $token<br>Expires: $expires<br>"; // Діагностика

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'uashared42.twinservers.net';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'info@annkyevents.pp.ua';
                    $mail->Password = ПАРОЛЬ;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;

                    $mail->setFrom('info@annkyevents.pp.ua', 'Управління подіями');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = 'Скидання пароля';
                    $resetLink = "https://annkyevents.pp.ua/reset_confirm.php?token=$token";
                    $mail->Body = "
                        <h2>Скидання пароля</h2>
                        <p>Перейдіть за <a href='$resetLink'>посиланням</a>, щоб скинути пароль.</p>
                        <p>Це посилання дійсне протягом 24 годин.</p>
                    ";
                    $mail->AltBody = "Перейдіть за посиланням, щоб скинути пароль: $resetLink";

                    $mail->send();
                    header("Location: confirm.php?email=" . urlencode($email));
                    exit;
                } catch (Exception $e) {
                    $error = "Не вдалося надіслати лист. Помилка: {$e->getMessage()}";
                }
            }
        } catch (PDOException $e) {
            $error = "Помилка бази даних: " . $e->getMessage();
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
                    <form action="reset.php" method="POST" class="left-side__form form">
                        <input type="hidden" name="submitted" value="1">
                        <label class="form__label">
                            <span class="form__title">Електронна пошта</span>
                            <input name="email" required class="form__input" type="email">
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
