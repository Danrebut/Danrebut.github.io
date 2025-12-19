<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);?>
<?php
$host = 'MySQL-8.4';      // или '127.0.0.1'
$user = 'root';
$pass = '';               // или 'root', если такой пароль
$db   = 'my_db';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        label{
            font-size:30px;
            
        }
        input{
            width:15%;
            padding: 10px;
        }
        input:focus { outline: none; border-color: #4CAF50; }
        .error-field { border-color: #d32f2f !important; box-shadow: 0 0 5px rgba(211,47,47,0.3); }
        .field-error { color: #d32f2f; font-size: 14px; margin-top: 5px; display: block; }
        .success { color: #2e7d32; background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Регистрационная форма</h1>

    <?php
        $field_errors = ['name' => '', 'email' => '', 'password' => ''];
        $name = $email = $password = '';

        if (isset($_POST['submit'])) {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Валидация
            if (empty($name)) {
                $field_errors['name'] = "Имя не может быть пустым";
            }
            if (empty($email)) {
                $field_errors['email'] = "Email не может быть пустым";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $field_errors['email'] = "Email в некорректном формате";
            }
            if (empty($password)) {
                $field_errors['password'] = "Пароль не может быть пустым";
            }




            // Если нет ошибок
            if (empty(array_filter($field_errors))) {

                // Экранируем данные для безопасности
                $name_safe  = mysqli_real_escape_string($conn, $name);
                $email_safe = mysqli_real_escape_string($conn, $email);

                // Хэшируем пароль
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $password_safe = mysqli_real_escape_string($conn, $password_hash);

                // SQL-запрос на добавление
                $sql = "
                    INSERT INTO users (name, email, password)
                    VALUES ('$name_safe', '$email_safe', '$password_safe')
                ";

                if (mysqli_query($conn, $sql)) {
                    echo '<div class="success">Регистрация успешна! Пользователь добавлен в БД.</div>';
                    // Можно очистить поля после успешной регистрации
                    $name = $email = $password = '';
                } else {
                    echo '<div class="error">Ошибка при сохранении в БД: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
                }
            }
        }
    ?>

    <form method="POST" action="">
        <label><b>Имя:</b></label><br>
        <input name="name" type="text" value="<?= htmlspecialchars($name) ?>"
            class="<?= !empty($field_errors['name']) ? 'error-field' : '' ?>" maxlength="100" required><br>

        <?php if ($field_errors['name']): ?>
            <span class="field-error"><?= $field_errors['name'] ?></span>
        <?php endif; ?>

        <label><b>E-mail:</b></label><br>
        <input name="email" type="text" value="<?= htmlspecialchars($email) ?>"
            class="<?= !empty($field_errors['email']) ? 'error-field' : '' ?>" maxlength="150" required><br>

        <?php if ($field_errors['email']): ?>

            <span class="field-error"><?= $field_errors['email'] ?></span>
        <?php endif; ?>

        <label><b>Пароль:<b></label><br>
        <input type="password" name="password" value="<?= htmlspecialchars($password) ?>" 
                class="<?= !empty($field_errors['password']) ? 'error-field' : '' ?>" minlength="6" maxlength="255" required>
        <?php if ($field_errors['password']): ?>
            <span class="field-error"><?= $field_errors['password'] ?></span>
        <?php endif; ?>

        <br><button style="margin-top:10px; padding:10px; width: 10%" type="submit" name="submit">Регистрация</button>
    </form>

    <?php if (isset($conn)) $conn->close(); ?>
</body>
</html>