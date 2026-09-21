<?php
require __DIR__ . '/functions.php';
requireAuth();
$user = currentUser();
$uid = (int)$user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'restore') {
        $t = removeFromArchive($uid, $id);
        if ($t) {
            addTask($uid, [
                'title' => $t['title'],
                'description' => $t['description'] ?? '',
                'date' => $t['date'] ?: date('Y-m-d'),
                'status' => $t['status'] ?: 'actual',
                'priority' => $t['priority'] ?: 'medium'
            ]);
        }
        redirect('archive.php');
    }
    if ($action === 'delete-forever') {
        removeFromArchive($uid, $id);
        redirect('archive.php');
    }
    if ($action === 'clear') {
        clearArchive($uid);
        redirect('archive.php');
    }
}

$archive = getArchive($uid);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Архив — Задачи на день</title>
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/archive.css">
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<div class="container-archive">

  <nav class="breadcrumbs">
    <a href="index.php">Главная</a>
    <span class="sep">/</span>
    <span class="current">Архив</span>
  </nav>

  <div class="page-header">
    <h1>Архив задач</h1>
    <div class="counter">Всего <span class="num" id="totalCount"><?= count($archive) ?></span></div>
  </div>

  <div class="toolbar">
    <div class="search">
      <input type="text" id="searchInput" placeholder="Поиск по названию...">
    </div>
    <select id="sortSelect">
      <option value="date-desc">Сначала новые</option>
      <option value="date-asc">Сначала старые</option>
      <option value="title">По названию</option>
    </select>
    <form id="clearAllForm" method="post" style="display:inline">
      <input type="hidden" name="action" value="clear">
      <button type="button" class="clear-btn" id="clearAllBtn">Очистить архив</button>
    </form>
  </div>

  <div class="archive-list" id="archiveList">
    <?php if (empty($archive)): ?>
      <div class="empty-state">
        <h2>Архив пуст</h2>
        <p>Здесь будут храниться удалённые задачи.</p>
        <a href="index.php" class="restore-btn" style="display:inline-block;">К задачам</a>
      </div>
    <?php else: foreach ($archive as $t):
      $isDone = ($t['status'] ?? '') === 'done';
      $statusL = $isDone ? 'Была: Выполнена' : 'Была: Актуальная';
      $statusC = $isDone ? 'done' : 'actual';
    ?>
      <div class="archive-card"
           data-id="<?= (int)$t['id'] ?>"
           data-title="<?= e($t['title']) ?>"
           data-archived-at="<?= e($t['archived_at']) ?>">
        <div class="archive-body">
          <div class="archive-title <?= $isDone ? 'done' : '' ?>"><?= e($t['title']) ?></div>
          <div class="archive-meta">
            <span> <?= e(formatRu($t['date'])) ?></span>
            <span class="badge <?= $statusC ?>"><?= e($statusL) ?></span>
            <span class="badge archived">В архиве</span>
          </div>
          <div class="archive-meta">
            <span> Удалено: <?= e(formatDateTimeRu($t['archived_at'])) ?></span>
          </div>
          <?php if (!empty($t['description'])): ?>
            <div class="archive-desc"><?= e($t['description']) ?></div>
          <?php endif; ?>
        </div>
        <div class="archive-actions">
          <form method="post" style="display:contents">
            <input type="hidden" name="action" value="restore">
            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
            <button type="submit" class="restore-btn">↩ Восстановить</button>
          </form>
          <form method="post" style="display:contents" onsubmit="return confirm('Удалить навсегда?')">
            <input type="hidden" name="action" value="delete-forever">
            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
            <button type="submit" class="delete-forever-btn">✕ Удалить навсегда</button>
          </form>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>
<script src="js/archive.js"></script>
</body>
</html>