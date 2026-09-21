<?php
require __DIR__ . '\functions.php';
if (isLoggedIn()) redirect('index.php');

$error = '';
$v = ['fullname'=>'','phone'=>'','position'=>'','login'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $position = $_POST['position'] ?? '';
    $login    = trim($_POST['login'] ?? '');
    $pass     = $_POST['password'] ?? '';
    $pass2    = $_POST['password2'] ?? '';

    $v = compact('fullname','phone','position','login');

    if ($fullname===''||$phone===''||$position===''||$login===''||$pass==='') {
        $error = 'Заполните все обязательные поля.';
    } elseif (!in_array($position, ['programmer','lawyer','economist'], true)) {
        $error = 'Выберите корректную должность.';
    } elseif (mb_strlen($pass) < 6) {
        $error = 'Пароль минимум 6 символов.';
    } elseif ($pass !== $pass2) {
        $error = 'Пароли не совпадают.';
    } elseif (findUserByLogin($login)) {
        $error = 'Логин уже занят.';
    } else {
        $newId = addUser([
            'fullname' => mb_substr($fullname, 0, 120),
            'phone'    => mb_substr($phone, 0, 30),
            'position' => $position,
            'login'    => mb_substr($login, 0, 60),
            'password_hash' => password_hash($pass, PASSWORD_DEFAULT),
        ]);
        $_SESSION['user_id'] = $newId;
        redirect('index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Регистрация — Задачи на день</title>
<link rel="stylesheet" href="css/auth.css">
</head>
<body class="login-page">
<div class="login-container">
  <div class="login-box">
    <h1>Регистрация</h1>

    <?php if ($error): ?>
      <div class="error-message"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="input-group">
        <label for="fullname">ФИО</label>
        <input type="text" id="fullname" name="fullname" value="<?= e($v['fullname']) ?>" required>
      </div>
      <div class="input-group">
        <label for="phone">Телефон</label>
        <input type="tel" id="phone" name="phone" value="<?= e($v['phone']) ?>" required>
      </div>
      <div class="input-group">
        <label for="position">Должность</label>
        <select id="position" name="position" required>
          <option value="" disabled <?= $v['position']===''?'selected':'' ?>>Выберите</option>
          <option value="programmer" <?= $v['position']==='programmer'?'selected':'' ?>>Программист</option>
          <option value="lawyer" <?= $v['position']==='lawyer'?'selected':'' ?>>Юрист</option>
          <option value="economist" <?= $v['position']==='economist'?'selected':'' ?>>Экономист</option>
        </select>
      </div>
      <div class="input-group">
        <label for="login">Логин</label>
        <input type="text" id="login" name="login" value="<?= e($v['login']) ?>" required autocomplete="username">
      </div>
      <div class="input-group">
        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password">
      </div>
      <div class="input-group">
        <label for="password2">Подтверждение пароля</label>
        <input type="password" id="password2" name="password2" required minlength="6" autocomplete="new-password">
      </div>
      <button type="submit" class="btn">Зарегистрироваться</button>
    </form>

    <div class="register-link">
      Уже есть аккаунт? <a href="login.php">Войти</a>
    </div>
  </div>
</div>
</body>
</html>