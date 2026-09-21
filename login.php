<?php
require __DIR__ . '\functions.php';
if (isLoggedIn()) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($login === '' || $pass === '') {
        $error = 'Заполните все поля.';
    } else {
        $u = findUserByLogin($login);
        if (!$u || !password_verify($pass, $u['password_hash'])) {
            $error = 'Неверный логин или пароль.';
        } else {
            $_SESSION['user_id'] = (int)$u['id'];
            redirect('index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Вход — Задачи на день</title>
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/auth.css">
</head>
<body class="login-page">
<div class="login-container">
  <div class="login-box">
  <h1>Вход в систему</h1>

    <?php if ($error): ?>
      <div class="error-message"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="input-group">
        <label for="login">Логин</label>
        <input type="text" id="login" name="login" required autocomplete="username">
      </div>
      <div class="input-group">
        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn">Войти</button>
    </form>

    <div class="register-link">
      Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
    </div>
  </div>
</div>
</body>
</html>