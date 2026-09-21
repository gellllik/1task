<?php
require __DIR__ . '/functions.php';
requireAuth();
$user = currentUser();
$uid = (int)$user['id'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $login    = trim($_POST['login'] ?? '');
    $position = $_POST['position'] ?? 'programmer';
    $birthdate = $_POST['birthdate'] ?? '';
    $city     = trim($_POST['city'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telegram = trim($_POST['telegram'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');

    $removePhoto = ($_POST['photoRemoveFlag'] ?? '') === '1';
    $photoData   = $_POST['photoData'] ?? '';

    $newPhoto = $user['photo'] ?? '';
    if ($removePhoto) $newPhoto = '';

    if ($photoData && preg_match('#^data:image/(png|jpeg|jpg|gif|webp);base64,#i', $photoData)) {
        if (strlen($photoData) <= 3 * 1024 * 1024) $newPhoto = $photoData;
    }

    if (!empty($_FILES['photoFile']['tmp_name']) && $_FILES['photoFile']['error'] === UPLOAD_ERR_OK) {
        $f = $_FILES['photoFile'];
        if ($f['size'] <= 2 * 1024 * 1024) {
            $mime = mime_content_type($f['tmp_name']) ?: '';
            if (in_array($mime, ['image/png','image/jpeg','image/gif','image/webp'], true)) {
                $newPhoto = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($f['tmp_name']));
            }
        }
    }

    if ($fullname === '' || $login === '') {
        $error = 'Заполните обязательные поля.';
    } else {
        // проверка уникальности логина
        $st = db()->prepare("SELECT id FROM users WHERE LOWER(login)=LOWER(?) AND id <> ? LIMIT 1");
        $st->execute([$login, $uid]);
        if ($st->fetch()) {
            $error = 'Логин уже занят.';
        } else {
            updateUser($uid, [
                'fullname'  => mb_substr($fullname, 0, 120),
                'login'     => mb_substr($login, 0, 60),
                'position'  => in_array($position, ['programmer','lawyer','economist'], true) ? $position : 'programmer',
                'birthdate' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate) ? $birthdate : null,
                'city'      => mb_substr($city, 0, 60),
                'phone'     => mb_substr($phone, 0, 30),
                'email'     => mb_substr($email, 0, 120),
                'telegram'  => mb_substr($telegram, 0, 60),
                'bio'       => mb_substr($bio, 0, 600),
                'photo'     => $newPhoto,
            ]);
            redirect('profile.php');
        }
    }
    $user = currentUser();
}

$uName = $user['fullname'] ?? '';
$uInit = initials($uName);
$uPhoto = $user['photo'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Редактирование профиля</title>
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/edit-profile.css">
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<div class="container-narrow">

  <nav class="breadcrumbs">
    <a href="index.php">Главная</a>
    <span class="sep">/</span>
    <a href="profile.php">Профиль</a>
    <span class="sep">/</span>
    <span class="current">Редактирование</span>
  </nav>

  <h1 style="color:#0c297a;margin-bottom:20px;">Редактирование профиля</h1>

  <?php if ($error): ?>
    <div class="alert error"><?= e($error) ?></div>
  <?php endif; ?>

  <form id="editForm" method="post" enctype="multipart/form-data">

    <div class="edit-card">
      <h3>📷 Фотография</h3>
      <div class="photo-edit">
        <div class="photo-preview" id="photoPreview">
          <?php if ($uPhoto): ?>
            <img src="<?= e($uPhoto) ?>" alt="">
            <span id="photoPreviewText" style="display:none"><?= e($uInit) ?></span>
          <?php else: ?>
            <span id="photoPreviewText"><?= e($uInit) ?></span>
          <?php endif; ?>
        </div>
        <div class="photo-controls">
          <label class="btn-file"> Загрузить<input type="file" id="photoInput" name="photoFile" accept="image/*"></label>
          <button type="button" class="btn-remove" id="removePhotoBtn">Удалить</button>
          <p style="margin-top:8px;font-size:0.8rem;color:#777;">JPG, PNG, GIF. До 2 МБ.</p>
        </div>
      </div>
      <input type="hidden" name="photoData" id="photoData" value="">
      <input type="hidden" name="photoRemoveFlag" id="photoRemoveFlag" value="">
    </div>

    <div class="edit-card">
      <h3> Основные данные</h3>
      <div class="form-group">
        <label for="fullname">ФИО</label>
        <input type="text" id="fullname" name="fullname" value="<?= e($user['fullname']) ?>" required maxlength="120">
      </div>
      <div class="form-group">
        <label for="login">Логин</label>
        <input type="text" id="login" name="login" value="<?= e($user['login']) ?>" required maxlength="60">
      </div>
      <div class="form-group">
        <label for="position">Должность</label>
        <select id="position" name="position" required>
          <option value="programmer" <?= $user['position']==='programmer'?'selected':'' ?>>Программист</option>
          <option value="lawyer" <?= $user['position']==='lawyer'?'selected':'' ?>>Юрист</option>
          <option value="economist" <?= $user['position']==='economist'?'selected':'' ?>>Экономист</option>
        </select>
      </div>
      <div class="form-group">
        <label for="birthdate">Дата рождения</label>
        <input type="date" id="birthdate" name="birthdate" value="<?= e($user['birthdate']) ?>">
      </div>
      <div class="form-group">
        <label for="city">Город</label>
        <input type="text" id="city" name="city" value="<?= e($user['city']) ?>" maxlength="60">
      </div>
    </div>

    <div class="edit-card">
      <h3> Контакты</h3>
      <div class="form-group">
        <label for="phone">Телефон</label>
        <input type="tel" id="phone" name="phone" value="<?= e($user['phone']) ?>">
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($user['email']) ?>">
      </div>
      <div class="form-group">
        <label for="telegram">Max</label>
        <input type="text" id="telegram" name="telegram" value="<?= e($user['telegram']) ?>" maxlength="60">
      </div>
    </div>

    <div class="edit-card">
      <h3> О себе</h3>
      <div class="form-group">
        <label for="bio">Краткое описание</label>
        <textarea id="bio" name="bio" maxlength="600"><?= e($user['bio']) ?></textarea>
      </div>
    </div>

    <div class="actions-row">
      <a href="profile.php" class="btn-secondary" style="display:inline-block;text-align:center;">Отмена</a>
      <button type="submit" class="btn-primary">Сохранить</button>
    </div>
  </form>
</div>
<script src="js/edit-profile.js"></script>
</body>
</html>