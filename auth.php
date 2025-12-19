<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);?>
<?php
$host = '127.0.0.1:3308';      // или '127.0.0.1'
$user = 'danburckin';
$pass = 'Zxcvbn12';               // или 'root', если такой пароль
$db   = 'danburckin';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}
?>
<?php
    $field_errors = ['email' => '', 'password' => ''];
    $email = $password = '';
    $login_error = '';

    // Если пользователь уже авторизован 
    if (isset($_SESSION['user_email'])) {
        $user_email = $_SESSION['user_email'];
        
        echo "<div class='dashboard'>";
        echo "<h2>Добро пожаловать, " . htmlspecialchars($user_email) . "!</h2>";
        echo "<p>Вы успешно авторизованы.</p>";
        echo "<form method='POST' style='display: inline-block;'>";
        echo "<button class='logout' type='submit' name='logout'>Выйти</button>";
        echo "</form>";
        echo "</div>";
        
        if (isset($_POST['logout'])) {
            session_destroy();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
        mysqli_close($conn);
        exit;
    }

    if (isset($_POST['submit'])) {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Валидация
        if (empty($email)) {
            $field_errors['email'] = "Email не может быть пустым";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $field_errors['email'] = "Email в некорректном формате";
        }
        if (empty($password)) {
            $field_errors['password'] = "Пароль не может быть пустым";
        }

        // Если нет ошибок валидации, проверяем в БД
        if (empty($field_errors['email']) && empty($field_errors['password'])) {
            $query = "SELECT email, password FROM users WHERE email = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($user = mysqli_fetch_assoc($result)) {
                // Проверяем пароль
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_email'] = $user['email'];
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $login_error = "Неверный пароль";
                }
            } else {
                $login_error = "Пользователь не найден";
            }
        }
    }
?>

<?php if ($login_error): ?>
<div class="error"><?= htmlspecialchars($login_error) ?></div>
<?php endif; ?>

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
    <h1>Вход на сайт</h1>

    <form method="POST" action="">
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

        <br><button style="margin-top:10px; padding:10px; width: 10%" type="submit" name="submit">Войти</button>
    </form>

    <?php if (isset($conn)) $conn->close(); ?>
</body>
</html>