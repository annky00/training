<?php
session_start();
$email = $_GET['email'] ?? 'вашу електронну адресу';
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управління подіями - Підтвердження</title>
    <link rel="stylesheet" href="css/forms.css">
    <link rel="icon" type="image/svg+xml" href="img/icons/logo.svg">
</head>
<body>
    <div class="wrapper">
        <div class="left-side">
            <div class="left-side__content">
                <h1 class="left-side__logo">Управління подіями</h1>
                <div class="left-side__login">
                    <h2 class="left-side__title left-side__title--reset">Успішно !</h2>
                    <p class="left-side__text left-side__text--reset">На вашу електронну адресу <?php echo htmlspecialchars($email); ?> було надіслано лист. Будь ласка, перевірте наявність листа та перейдіть за посиланням, щоб скинути пароль.</p>
                    <div class="left-side__bottom">
                        <a href="login.php" class="form__button">На головну</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="right-side"></div>
    </div>
</body>
</html>