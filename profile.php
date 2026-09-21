<?php
require __DIR__ . '\functions.php';
requireAuth();
$user = currentUser();
$uid = (int)$user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_account') {
    deleteUser($uid);
    $_SESSION = [];
    session_destroy();
    redirect('login.php');
}

$uName = $user['fullname'] ?? '';
$uInit = initials($uName);
$uPhoto = $user['photo'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Профиль — Задачи на день</title>
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/profile.css">
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<div class="container">
  <nav class="breadcrumbs">
    <a href="index.php">Главная</a>
    <span class="sep">/</span>
    <span class="current">Профиль</span>
  </nav>

  <div class="profile-wrap">
    <div class="avatar-box">
      <?php if ($uPhoto): ?>
        <img src="<?= e($uPhoto) ?>" alt="">
      <?php else: ?>
        <?= e($uInit) ?>
      <?php endif; ?>
      <span class="status-dot"></span>
    </div>

    <div class="profile-info">
      <h2><?= e($user['fullname']) ?></h2>
      <div class="position-badge"><?= e(positionLabel($user['position'])) ?></div>

      <div class="info-item">
        <div class="info-label">Логин</div>
        <div class="info-value"><?= e($user['login']) ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Телефон</div>
        <div class="info-value <?= empty($user['phone'])?'empty':'' ?>">
          <?= e($user['phone'] ?: 'Не указан') ?>
        </div>
      </div>
      <div class="info-item">
        <div class="info-label">Email</div>
        <div class="info-value <?= empty($user['email'])?'empty':'' ?>">
          <?= e($user['email'] ?: 'Не указан') ?>
        </div>
      </div>
      <div class="info-item">
        <div class="info-label">Max</div>
        <div class="info-value <?= empty($user['telegram'])?'empty':'' ?>">
          <?= e($user['telegram'] ?: 'Не указан') ?>
        </div>
      </div>
      <div class="info-item">
        <div class="info-label">Город</div>
        <div class="info-value <?= empty($user['city'])?'empty':'' ?>">
          <?= e($user['city'] ?: 'Не указан') ?>
        </div>
      </div>
      <div class="info-item">
        <div class="info-label">Дата рождения</div>
        <div class="info-value <?= empty($user['birthdate'])?'empty':'' ?>">
          <?= e($user['birthdate'] ? formatRu($user['birthdate']) : 'Не указана') ?>
        </div>
      </div>
      <div class="info-item">
        <div class="info-label">В системе с</div>
        <div class="info-value"><?= e(formatDateTimeRu($user['registered_at'])) ?></div>
      </div>
    </div>
  </div>

  <div class="info-card">
    <h3>О себе</h3>
    <div class="info-value <?= empty($user['bio'])?'empty':'' ?>">
      <?= e($user['bio'] ?: 'Информация не заполнена') ?>
    </div>
  </div>

  <div class="actions-row">
    <form method="post" onsubmit="return confirm('Удалить аккаунт? Действие необратимо.')">
      <input type="hidden" name="action" value="delete_account">
      <button type="submit" class="btn-danger">Удалить аккаунт</button>
    </form>
    <a href="edit-profile.php" class="btn-primary">Изменить данные</a>
  </div>
</div>
</body>
</html>