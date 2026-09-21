<?php
require_once __DIR__ . '/functions.php';

$u = currentUser();

$fullName = '';
if (is_array($u)) {
    $fullName = $u['full_name']
        ?? $u['fio']
        ?? $u['fullname']
        ?? $u['name']
        ?? trim(($u['surname'] ?? '') . ' ' . ($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
    $fullName = trim((string)$fullName);
}
if ($fullName === '') {
    $fullName = $u['login'] ?? 'Пользователь';
}

$initials = '';
foreach (preg_split('/\s+/u', $fullName) as $part) {
    if ($part !== '' && mb_strlen($initials, 'UTF-8') < 2) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8');
    }
}
$initials = $initials !== '' ? $initials : 'П';
?>
<header>
  <div class="header-left">
    <div class="logo">
  <img src="picture/1.jpg" alt="Логотип">
</div>
    <div class="app-title">Задачи на день</div>
  </div>

  <div class="header-right">
    <a href="profile.php" class="user-btn" title="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>">
      <span class="user-avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
      <span class="user-name"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></span>
    </a>
    <a href="logout.php" class="logout-btn">Выход</a>
  </div>
</header>